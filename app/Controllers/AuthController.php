<?php
namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Session;
use App\Models\User;

/**
 * AuthController - контроллер аутентификации
 */
class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Страница логина
     */
    public function loginPage(): void
    {
        $this->requireGuest();
        $this->view('auth/login');
    }

    /**
     * Страница регистрации
     */
    public function registerPage(): void
    {
        $this->requireGuest();
        $this->view('auth/register');
    }

    /**
     * Обработка логина
     */
    public function login(): void
    {
        $request = new Request();

        $email = $request->input('email');
        $password = $request->input('password');

        // Валидация
        $errors = [];

        if (empty($email)) {
            $errors[] = 'Email обязателен';
        }

        if (empty($password)) {
            $errors[] = 'Пароль обязателен';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old_email', $email);
            $this->redirect('/login');
            return;
        }

        // Поиск пользователя
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            Session::flash('errors', ['Неверный email или пароль']);
            Session::flash('old_email', $email);
            $this->redirect('/login');
            return;
        }

        // Проверка пароля
        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            Session::flash('errors', ['Неверный email или пароль']);
            Session::flash('old_email', $email);
            $this->redirect('/login');
            return;
        }

        // Проверка блокировки
        if ($user['is_blocked']) {
            Session::flash('errors', ['Ваш аккаунт заблокирован']);
            $this->redirect('/login');
            return;
        }

        // Авторизация
        Session::login($user['id']);
        Session::flash('success', 'Добро пожаловать!');
        $this->redirect('/dashboard');
    }

    /**
     * Обработка регистрации
     */
    public function register(): void
    {
        $request = new Request();

        $name = trim($request->input('name'));
        $email = trim($request->input('email'));
        $password = $request->input('password');
        $passwordConfirm = $request->input('password_confirm');
        $role = $request->input('role', 'CLIENT');

        // Валидация
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Имя обязательно';
        }

        if (empty($email)) {
            $errors[] = 'Email обязателен';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Некорректный email';
        }

        if (empty($password)) {
            $errors[] = 'Пароль обязателен';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Пароль должен быть минимум 6 символов';
        }

        if ($password !== $passwordConfirm) {
            $errors[] = 'Пароли не совпадают';
        }

        // Проверка существования email
        if ($this->userModel->emailExists($email)) {
            $errors[] = 'Пользователь с таким email уже существует';
        }

        // Валидация роли
        if (!in_array($role, ['CLIENT', 'PSYCHOLOGIST'])) {
            $role = 'CLIENT';
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old_name', $name);
            Session::flash('old_email', $email);
            Session::flash('old_role', $role);
            $this->redirect('/register');
            return;
        }

        // Создание пользователя
        try {
            $userId = $this->userModel->createUser([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
            ]);

            // Авторизация
            Session::login($userId);
            Session::flash('success', 'Регистрация успешна!');
            $this->redirect('/dashboard');
        } catch (\Exception $e) {
            Session::flash('errors', ['Ошибка регистрации: ' . $e->getMessage()]);
            $this->redirect('/register');
        }
    }

    /**
     * Выход из системы
     */
    public function logout(): void
    {
        Session::logout();
        Session::flash('success', 'Вы вышли из системы');
        $this->redirect('/');
    }
}
