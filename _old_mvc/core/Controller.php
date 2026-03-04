<?php
namespace Core;

/**
 * Controller - базовый класс для всех контроллеров
 */
class Controller
{
    /**
     * Отобразить view
     */
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    /**
     * Редирект на другую страницу
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Редирект назад
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    /**
     * Вернуть JSON ответ
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Проверка авторизации
     */
    protected function requireAuth(): void
    {
        if (!Session::isAuthenticated()) {
            Session::flash('error', 'Необходимо войти в систему');
            $this->redirect('/login');
        }
    }

    /**
     * Проверка гостя (не авторизован)
     */
    protected function requireGuest(): void
    {
        if (Session::isAuthenticated()) {
            $this->redirect('/dashboard');
        }
    }
}
