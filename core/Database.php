<?php
namespace Core;

use PDO;
use PDOException;

/**
 * Database - подключение к MySQL через PDO
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Получить singleton PDO соединение
     */
    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $config = require CONFIG_PATH . '/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Выполнить SELECT запрос
     */
    public static function query(string $sql, array $params = []): array
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Выполнить SELECT запрос и получить одну строку
     */
    public static function queryOne(string $sql, array $params = []): ?array
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Выполнить INSERT, UPDATE, DELETE
     */
    public static function execute(string $sql, array $params = []): bool
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Получить ID последней вставленной записи
     */
    public static function lastInsertId(): string
    {
        return self::connect()->lastInsertId();
    }
}
