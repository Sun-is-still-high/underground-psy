<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\Request;
use App\Models\User;
use App\Models\ClientCase;
use App\Models\ProblemType;

/**
 * CaseSearchController - поиск кейсов для психологов
 */
class CaseSearchController extends Controller
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
     * Страница поиска кейсов
     */
    public function index(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $request = new Request();

        // Получаем фильтры из GET параметров
        $filters = [
            'problem_type_id' => $request->get('problem_type') ? (int) $request->get('problem_type') : null,
            'budget_type' => $request->get('budget_type'),
            'limit' => 50
        ];

        // Получаем типы проблем для фильтра
        $problemTypes = $this->problemTypeModel->getActive();

        // Статистика по типам проблем (количество открытых кейсов)
        $stats = $this->caseModel->getStatsByProblemType();

        // Поиск кейсов
        $cases = $this->caseModel->searchOpen($filters);

        $this->view('psychologist/cases/search', [
            'user' => $user,
            'cases' => $cases,
            'problemTypes' => $problemTypes,
            'stats' => $stats,
            'filters' => [
                'problem_type' => $filters['problem_type_id'],
                'budget_type' => $filters['budget_type']
            ]
        ]);
    }

    /**
     * Просмотр кейса психологом
     */
    public function show(int $id): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $case = $this->caseModel->getWithDetails($id);

        if (!$case) {
            Session::flash('error', 'Кейс не найден');
            $this->redirect('/psychologist/cases');
            return;
        }

        // Проверяем, откликался ли уже психолог
        $hasResponded = $this->caseModel->hasResponse($id, $user['id']);

        $this->view('psychologist/cases/show', [
            'user' => $user,
            'case' => $case,
            'hasResponded' => $hasResponded
        ]);
    }

    /**
     * Отправить отклик на кейс
     */
    public function respond(int $id): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $case = $this->caseModel->getWithDetails($id);

        if (!$case) {
            Session::flash('error', 'Кейс не найден');
            $this->redirect('/psychologist/cases');
            return;
        }

        if ($case['status'] !== 'OPEN') {
            Session::flash('error', 'Кейс уже закрыт или в работе');
            $this->redirect('/psychologist/cases/' . $id);
            return;
        }

        // Проверяем, не откликался ли уже
        if ($this->caseModel->hasResponse($id, $user['id'])) {
            Session::flash('error', 'Вы уже откликнулись на этот кейс');
            $this->redirect('/psychologist/cases/' . $id);
            return;
        }

        $request = new Request();

        $message = trim($request->input('message', ''));
        $proposedPrice = $request->input('proposed_price') ? (float) $request->input('proposed_price') : null;

        if (empty($message)) {
            Session::flash('error', 'Напишите сообщение клиенту');
            Session::flash('old', $request->post());
            $this->redirect('/psychologist/cases/' . $id);
            return;
        }

        $this->caseModel->addResponse($id, $user['id'], $message, $proposedPrice);

        Session::flash('success', 'Ваш отклик отправлен клиенту!');
        $this->redirect('/psychologist/cases/' . $id);
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
