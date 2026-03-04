<?php
namespace App\Models;

use Core\Model;

/**
 * User Model - модель пользователя
 */
class User extends Model
{
    protected string $table = 'users';

    /**
     * Найти пользователя по email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email);
    }

    /**
     * Создать нового пользователя
     */
    public function createUser(array $data): int
    {
        // Хешируем пароль
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);

        // Устанавливаем роль по умолчанию, если не указана
        if (!isset($data['role'])) {
            $data['role'] = 'CLIENT';
        }

        return $this->create($data);
    }

    /**
     * Проверить пароль пользователя
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Проверка существования email
     */
    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    /**
     * Получить пользователя по ID
     */
    public function getUserById(int $id): ?array
    {
        return $this->find($id);
    }
}
