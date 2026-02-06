<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Request;
use App\Models\User;
use App\Models\ClientCase;
use App\Models\ProblemType;

/**
 * CaseController - управление кейсами для клиентов
 */
class CaseController extends Controller
{
    private ClientCase $caseModel;
    private ProblemType $problemTypeModel;
    private User $userModel;

    public function __construct()
    {
        $this->caseModel = new ClientCase();
        $this->problemTypeModel = new ProblemType();
        $this->userModel = new User();
    }

    /**
     * Список кейсов клиента
     */
    public function index(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'CLIENT') {
            $this->redirect('/dashboard');
            return;
        }

        $cases = $this->caseModel->getByClientId($user['id']);

        $this->view('client/cases/index', [
            'user' => $user,
            'cases' => $cases
        ]);
    }

    /**
     * Форма создания кейса
     */
    public function create(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'CLIENT') {
            $this->redirect('/dashboard');
            return;
        }

        $problemTypes = $this->problemTypeModel->getActive();

        $this->view('client/cases/create', [
            'user' => $user,
            'problemTypes' => $problemTypes
        ]);
    }

    /**
     * Сохранение кейса
     */
    public function store(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'CLIENT') {
            $this->redirect('/dashboard');
            return;
        }

        $request = new Request();

        // Валидация
        $title = trim($request->input('title', ''));
        $description = trim($request->input('description', ''));
        $problemTypeId = (int) $request->input('problem_type_id', 0);
        $isAnonymous = $request->input('is_anonymous') === '1';
        $budgetType = $request->input('budget_type', 'NEGOTIABLE');
        $budgetAmount = $request->input('budget_amount') ? (float) $request->input('budget_amount') : null;

        $errors = [];

        if (empty($title)) {
            $errors[] = 'Укажите краткое описание проблемы';
        }
        if (mb_strlen($title) > 255) {
            $errors[] = 'Заголовок слишком длинный (максимум 255 символов)';
        }
        if (empty($description)) {
            $errors[] = 'Опишите вашу ситуацию подробнее';
        }
        if ($problemTypeId <= 0) {
            $errors[] = 'Выберите тип проблемы';
        }
        if (!in_array($budgetType, ['PAID', 'REVIEW', 'NEGOTIABLE'])) {
            $budgetType = 'NEGOTIABLE';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $request->post());
            $this->redirect('/client/cases/create');
            return;
        }

        // Создаём кейс
        $caseId = $this->caseModel->createCase([
            'client_id' => $user['id'],
            'problem_type_id' => $problemTypeId,
            'title' => $title,
            'description' => $description,
            'is_anonymous' => $isAnonymous ? 1 : 0,
            'budget_type' => $budgetType,
            'budget_amount' => $budgetAmount
        ]);

        Session::flash('success', 'Ваш запрос успешно опубликован! Психологи смогут откликнуться на него.');
        $this->redirect('/client/cases');
    }

    /**
     * Просмотр кейса с откликами
     */
    public function show(int $id): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'CLIENT') {
            $this->redirect('/dashboard');
            return;
        }

        $case = $this->caseModel->getWithDetails($id);

        if (!$case || $case['client_id'] !== $user['id']) {
            Session::flash('error', 'Кейс не найден');
            $this->redirect('/client/cases');
            return;
        }

        $responses = $this->caseModel->getResponses($id);

        $this->view('client/cases/show', [
            'user' => $user,
            'case' => $case,
            'responses' => $responses
        ]);
    }

    /**
     * Принять отклик психолога
     */
    public function acceptResponse(int $caseId, int $responseId): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'CLIENT') {
            $this->redirect('/dashboard');
            return;
        }

        $case = $this->caseModel->getWithDetails($caseId);

        if (!$case || $case['client_id'] !== $user['id']) {
            Session::flash('error', 'Кейс не найден');
            $this->redirect('/client/cases');
            return;
        }

        if ($case['status'] !== 'OPEN') {
            Session::flash('error', 'Кейс уже закрыт или в работе');
            $this->redirect('/client/cases/' . $caseId);
            return;
        }

        $this->caseModel->acceptResponse($responseId, $caseId);

        Session::flash('success', 'Вы приняли отклик! Психолог получит уведомление.');
        $this->redirect('/client/cases/' . $caseId);
    }

    /**
     * Закрыть кейс
     */
    public function close(int $id): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'CLIENT') {
            $this->redirect('/dashboard');
            return;
        }

        $case = $this->caseModel->getWithDetails($id);

        if (!$case || $case['client_id'] !== $user['id']) {
            Session::flash('error', 'Кейс не найден');
            $this->redirect('/client/cases');
            return;
        }

        $this->caseModel->update($id, [
            'status' => 'CLOSED',
            'closed_at' => date('Y-m-d H:i:s')
        ]);

        Session::flash('success', 'Кейс закрыт');
        $this->redirect('/client/cases');
    }

    /**
     * Получить текущего пользователя
     */
    private function getUser(): array
    {
        $user = $this->userModel->getUserById(Session::userId());
        if (!$user) {
            Session::logout();
            $this->redirect('/login');
            exit;
        }
        return $user;
    }
}
