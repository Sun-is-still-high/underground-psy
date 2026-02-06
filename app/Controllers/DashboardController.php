<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\User;
use App\Services\IntervisionService;

/**
 * DashboardController - контроллер личного кабинета
 */
class DashboardController extends Controller
{
    /**
     * Главная страница дашборда
     */
    public function index(): void
    {
        // Проверяем авторизацию
        $this->requireAuth();

        // Получаем данные пользователя
        $userModel = new User();
        $user = $userModel->getUserById(Session::userId());

        if (!$user) {
            Session::logout();
            $this->redirect('/login');
            return;
        }

        $this->view('dashboard', [
            'user' => $user
        ]);
    }

    /**
     * Статус интервизий для психолога
     */
    public function intervisionStatus(): void
    {
        $this->requireAuth();

        $userModel = new User();
        $user = $userModel->getUserById(Session::userId());

        if (!$user) {
            Session::logout();
            $this->redirect('/login');
            return;
        }

        // Только для психологов
        if ($user['role'] !== 'PSYCHOLOGIST') {
            $this->redirect('/dashboard');
            return;
        }

        $intervisionService = new IntervisionService();
        $status = $intervisionService->getPsychologistStatus($user['id']);

        $this->view('psychologist/intervision-status', [
            'user' => $user,
            'status' => $status
        ]);
    }
}
