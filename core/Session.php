<?php
namespace Core;

/**
 * Session - работа с PHP сессиями
 */
class Session
{
    /**
     * Запустить сессию
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Сохранить значение в сессию
     */
    public static function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Получить значение из сессии
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Проверить существование ключа в сессии
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Удалить значение из сессии
     */
    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Очистить всю сессию
     */
    public static function flush(): void
    {
        $_SESSION = [];
    }

    /**
     * Уничтожить сессию полностью
     */
    public static function destroy(): void
    {
        self::flush();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Flash-сообщение (сохраняется только на один запрос)
     */
    public static function flash(string $key, $value): void
    {
        self::put('_flash_' . $key, $value);
    }

    /**
     * Получить flash-сообщение
     */
    public static function getFlash(string $key, $default = null)
    {
        $value = self::get('_flash_' . $key, $default);
        self::forget('_flash_' . $key);
        return $value;
    }

    /**
     * Получить ID пользователя из сессии
     */
    public static function userId(): ?int
    {
        return self::get('user_id');
    }

    /**
     * Проверка, авторизован ли пользователь
     */
    public static function isAuthenticated(): bool
    {
        return self::has('user_id');
    }

    /**
     * Установить пользователя в сессию
     */
    public static function login(int $userId): void
    {
        self::put('user_id', $userId);
    }

    /**
     * Выйти из системы
     */
    public static function logout(): void
    {
        self::forget('user_id');
    }
}
