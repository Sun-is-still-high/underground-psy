<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Database;
use App\Models\User;
use App\Models\IntervisionGroup;
use App\Models\IntervisionSession;
use App\Models\IntervisionParticipant;
use App\Models\IntervisionAttendance;
use App\Services\IntervisionService;

class IntervisionController extends Controller
{
    private IntervisionGroup $groupModel;
    private IntervisionSession $sessionModel;
    private IntervisionParticipant $participantModel;
    private IntervisionAttendance $attendanceModel;
    private IntervisionService $intervisionService;
    private User $userModel;

    public function __construct()
    {
        $this->groupModel = new IntervisionGroup();
        $this->sessionModel = new IntervisionSession();
        $this->participantModel = new IntervisionParticipant();
        $this->attendanceModel = new IntervisionAttendance();
        $this->intervisionService = new IntervisionService();
        $this->userModel = new User();
    }

    /**
     * Проверка роли админа
     */
    private function requireAdmin(): void
    {
        $this->requireAuth();
        $user = $this->userModel->find(Session::userId());

        if (!$user || $user['role'] !== 'ADMIN') {
            Session::flash('error', 'Доступ запрещён');
            $this->redirect('/dashboard');
        }
    }

    // ==================== ГРУППЫ ====================

    /**
     * Список групп
     */
    public function groups(): void
    {
        $this->requireAdmin();

        $groups = $this->groupModel->getAllActive();

        $this->view('admin/intervision/groups/index', [
            'groups' => $groups
        ]);
    }

    /**
     * Форма создания группы
     */
    public function createGroup(): void
    {
        $this->requireAdmin();
        $this->view('admin/intervision/groups/create');
    }

    /**
     * Сохранение группы
     */
    public function storeGroup(): void
    {
        $this->requireAdmin();
        $request = new Request();

        $name = trim($request->input('name'));
        $description = trim($request->input('description'));
        $maxParticipants = (int)$request->input('max_participants', 10);

        $errors = [];
        if (empty($name)) {
            $errors[] = 'Название группы обязательно';
        }
        if ($maxParticipants < 2 || $maxParticipants > 50) {
            $errors[] = 'Количество участников должно быть от 2 до 50';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $request->all());
            $this->redirect('/admin/intervision/groups/create');
            return;
        }

        $groupId = $this->groupModel->create([
            'name' => $name,
            'description' => $description,
            'max_participants' => $maxParticipants,
            'created_by' => Session::userId()
        ]);

        Session::flash('success', 'Группа успешно создана');
        $this->redirect('/admin/intervision/groups/' . $groupId);
    }

    /**
     * Просмотр группы
     */
    public function showGroup(string $id): void
    {
        $this->requireAdmin();

        $id = (int)$id;
        $stats = $this->intervisionService->getGroupStats($id);
        if (!$stats['group']) {
            Session::flash('error', 'Группа не найдена');
            $this->redirect('/admin/intervision/groups');
            return;
        }

        $sessions = $this->sessionModel->getByGroup($id);
        $availablePsychologists = $this->getAvailablePsychologists($id);

        $this->view('admin/intervision/groups/show', [
            'group' => $stats['group'],
            'stats' => $stats,
            'sessions' => $sessions,
            'availablePsychologists' => $availablePsychologists
        ]);
    }

    /**
     * Форма редактирования группы
     */
    public function editGroup(string $id): void
    {
        $this->requireAdmin();

        $id = (int)$id;
        $group = $this->groupModel->find($id);
        if (!$group) {
            Session::flash('error', 'Группа не найдена');
            $this->redirect('/admin/intervision/groups');
            return;
        }

        $this->view('admin/intervision/groups/edit', [
            'group' => $group
        ]);
    }

    /**
     * Обновление группы
     */
    public function updateGroup(string $id): void
    {
        $this->requireAdmin();
        $request = new Request();

        $id = (int)$id;
        $name = trim($request->input('name'));
        $description = trim($request->input('description'));
        $maxParticipants = (int)$request->input('max_participants', 10);

        $errors = [];
        if (empty($name)) {
            $errors[] = 'Название группы обязательно';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            $this->redirect('/admin/intervision/groups/' . $id . '/edit');
            return;
        }

        $this->groupModel->update($id, [
            'name' => $name,
            'description' => $description,
            'max_participants' => $maxParticipants
        ]);

        Session::flash('success', 'Группа обновлена');
        $this->redirect('/admin/intervision/groups/' . $id);
    }

    /**
     * Деактивация группы
     */
    public function deleteGroup(string $id): void
    {
        $this->requireAdmin();

        $id = (int)$id;
        $this->groupModel->deactivate($id);

        Session::flash('success', 'Группа деактивирована');
        $this->redirect('/admin/intervision/groups');
    }

    // ==================== УЧАСТНИКИ ====================

    /**
     * Добавить психолога в группу
     */
    public function addParticipant(string $groupId): void
    {
        $this->requireAdmin();
        $request = new Request();

        $groupId = (int)$groupId;
        $psychologistId = (int)$request->input('psychologist_id');

        $group = $this->groupModel->find($groupId);
        if (!$group) {
            Session::flash('error', 'Группа не найдена');
            $this->redirect('/admin/intervision/groups');
            return;
        }

        $psychologist = $this->userModel->find($psychologistId);
        if (!$psychologist || $psychologist['role'] !== 'PSYCHOLOGIST') {
            Session::flash('error', 'Психолог не найден');
            $this->redirect('/admin/intervision/groups/' . $groupId);
            return;
        }

        if (!$this->groupModel->hasAvailableSpots($groupId)) {
            Session::flash('error', 'В группе нет свободных мест');
            $this->redirect('/admin/intervision/groups/' . $groupId);
            return;
        }

        if ($this->participantModel->isInGroup($groupId, $psychologistId)) {
            Session::flash('error', 'Психолог уже состоит в этой группе');
            $this->redirect('/admin/intervision/groups/' . $groupId);
            return;
        }

        $this->participantModel->addToGroup($groupId, $psychologistId);

        Session::flash('success', 'Психолог добавлен в группу');
        $this->redirect('/admin/intervision/groups/' . $groupId);
    }

    /**
     * Удалить психолога из группы
     */
    public function removeParticipant(string $groupId, string $odontologyIds): void
    {
        $this->requireAdmin();

        $groupId = (int)$groupId;
        $psychologistId = (int)$odontologyIds;

        $this->participantModel->removeFromGroup($groupId, $psychologistId);

        Session::flash('success', 'Психолог удалён из группы');
        $this->redirect('/admin/intervision/groups/' . $groupId);
    }

    // ==================== СЕССИИ ====================

    /**
     * Форма создания сессии
     */
    public function createSession(string $groupId): void
    {
        $this->requireAdmin();

        $groupId = (int)$groupId;
        $group = $this->groupModel->find($groupId);
        if (!$group) {
            Session::flash('error', 'Группа не найдена');
            $this->redirect('/admin/intervision/groups');
            return;
        }

        $this->view('admin/intervision/sessions/create', [
            'group' => $group
        ]);
    }

    /**
     * Сохранение сессии
     */
    public function storeSession(string $groupId): void
    {
        $this->requireAdmin();
        $request = new Request();

        $groupId = (int)$groupId;
        $topic = trim($request->input('topic'));
        $description = trim($request->input('description'));
        $scheduledAt = $request->input('scheduled_at');
        $duration = (int)$request->input('duration_minutes', 90);
        $meetingLink = trim($request->input('meeting_link'));

        $errors = [];
        if (empty($topic)) {
            $errors[] = 'Тема обязательна';
        }
        if (empty($scheduledAt)) {
            $errors[] = 'Дата и время обязательны';
        }
        if (!empty($scheduledAt) && strtotime($scheduledAt) < time()) {
            $errors[] = 'Нельзя запланировать сессию в прошлом';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $request->all());
            $this->redirect('/admin/intervision/groups/' . $groupId . '/sessions/create');
            return;
        }

        $sessionId = $this->intervisionService->createSession([
            'group_id' => $groupId,
            'topic' => $topic,
            'description' => $description,
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $duration,
            'meeting_link' => $meetingLink
        ]);

        Session::flash('success', 'Сессия запланирована');
        $this->redirect('/admin/intervision/sessions/' . $sessionId);
    }

    /**
     * Просмотр сессии
     */
    public function showSession(string $id): void
    {
        $this->requireAdmin();

        $id = (int)$id;
        $session = $this->sessionModel->findWithAttendance($id);
        if (!$session) {
            Session::flash('error', 'Сессия не найдена');
            $this->redirect('/admin/intervision/groups');
            return;
        }

        $group = $this->groupModel->find($session['group_id']);

        $this->view('admin/intervision/sessions/show', [
            'session' => $session,
            'group' => $group
        ]);
    }

    /**
     * Форма отметки посещаемости
     */
    public function attendanceForm(string $sessionId): void
    {
        $this->requireAdmin();

        $sessionId = (int)$sessionId;
        $session = $this->sessionModel->findWithAttendance($sessionId);
        if (!$session) {
            Session::flash('error', 'Сессия не найдена');
            $this->redirect('/admin/intervision/groups');
            return;
        }

        $group = $this->groupModel->find($session['group_id']);

        $this->view('admin/intervision/sessions/attendance', [
            'session' => $session,
            'group' => $group
        ]);
    }

    /**
     * Сохранение посещаемости
     */
    public function saveAttendance(string $sessionId): void
    {
        $this->requireAdmin();
        $request = new Request();

        $sessionId = (int)$sessionId;
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            Session::flash('error', 'Сессия не найдена');
            $this->redirect('/admin/intervision/groups');
            return;
        }

        $attendedIds = $request->input('attended', []);
        if (!is_array($attendedIds)) {
            $attendedIds = [];
        }
        $attendedIds = array_map('intval', $attendedIds);

        $this->attendanceModel->markBulkAttendance(
            $sessionId,
            $attendedIds,
            Session::userId()
        );

        if (in_array($session['status'], ['SCHEDULED', 'IN_PROGRESS'])) {
            $this->sessionModel->complete($sessionId);
        }

        Session::flash('success', 'Посещаемость сохранена');
        $this->redirect('/admin/intervision/sessions/' . $sessionId);
    }

    /**
     * Изменение статуса сессии
     */
    public function changeSessionStatus(string $id): void
    {
        $this->requireAdmin();
        $request = new Request();

        $id = (int)$id;
        $status = $request->input('status');
        $reason = $request->input('reason');

        $validStatuses = ['SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'];
        if (!in_array($status, $validStatuses)) {
            Session::flash('error', 'Недопустимый статус');
            $this->back();
            return;
        }

        $this->sessionModel->changeStatus($id, $status, $reason);

        Session::flash('success', 'Статус изменён');
        $this->redirect('/admin/intervision/sessions/' . $id);
    }

    // ==================== ВСПОМОГАТЕЛЬНЫЕ ====================

    /**
     * Получить психологов, не состоящих в группе
     */
    private function getAvailablePsychologists(int $groupId): array
    {
        $sql = "SELECT u.id, u.name, u.email
                FROM users u
                WHERE u.role = 'PSYCHOLOGIST'
                AND u.is_blocked = 0
                AND u.id NOT IN (
                    SELECT ip.psychologist_id
                    FROM intervision_participants ip
                    WHERE ip.group_id = :group_id AND ip.is_active = 1
                )
                ORDER BY u.name";
        return Database::query($sql, ['group_id' => $groupId]);
    }
}
