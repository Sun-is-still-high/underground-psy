<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\User;

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
}
