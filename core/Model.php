<?php
namespace Core;

use PDO;

/**
 * Model - базовый класс для всех моделей
 */
abstract class Model
{
    protected string $table; // Имя таблицы
    protected string $primaryKey = 'id'; // Первичный ключ

    /**
     * Получить PDO подключение
     */
    protected function db(): PDO
    {
        return Database::connect();
    }

    /**
     * Найти запись по ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return Database::queryOne($sql, ['id' => $id]);
    }

    /**
     * Найти все записи
     */
    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        return Database::query($sql);
    }

    /**
     * Найти запись по условию
     */
    public function where(string $column, $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        return Database::queryOne($sql, ['value' => $value]);
    }

    /**
     * Найти все записи по условию
     */
    public function whereAll(string $column, $value): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        return Database::query($sql, ['value' => $value]);
    }

    /**
     * Создать новую запись
     */
    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        Database::execute($sql, $data);

        return (int) Database::lastInsertId();
    }

    /**
     * Обновить запись
     */
    public function update(int $id, array $data): bool
    {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :{$key}";
        }
        $setString = implode(', ', $set);

        $sql = "UPDATE {$this->table} SET {$setString} WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;

        return Database::execute($sql, $data);
    }

    /**
     * Удалить запись
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return Database::execute($sql, ['id' => $id]);
    }
}
