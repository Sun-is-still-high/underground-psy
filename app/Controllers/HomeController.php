<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;

/**
 * HomeController - контроллер главной страницы
 */
class HomeController extends Controller
{
    /**
     * Главная страница
     */
    public function index(): void
    {
        $user = null;

        // Если пользователь авторизован, получаем его данные
        if (Session::isAuthenticated()) {
            $userModel = new \App\Models\User();
            $user = $userModel->getUserById(Session::userId());
        }

        $this->view('home', [
            'user' => $user
        ]);
    }
}
