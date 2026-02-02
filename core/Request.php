<?php
namespace Core;

/**
 * Request - работа с HTTP-запросами
 */
class Request
{
    /**
     * Получить HTTP метод запроса
     */
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Получить URI запроса
     */
    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Убираем query string (?foo=bar)
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }

        return $uri;
    }

    /**
     * Получить все GET параметры
     */
    public function query(): array
    {
        return $_GET;
    }

    /**
     * Получить конкретный GET параметр
     */
    public function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Получить все POST данные
     */
    public function post(): array
    {
        return $_POST;
    }

    /**
     * Получить конкретное POST значение
     */
    public function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Проверка, является ли запрос POST
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Проверка, является ли запрос GET
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Получить все данные запроса (GET + POST)
     */
    public function all(): array
    {
        return array_merge($this->query(), $this->post());
    }
}
