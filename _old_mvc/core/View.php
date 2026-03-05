<?php
namespace Core;

/**
 * View - рендеринг шаблонов
 */
class View
{
    /**
     * Отрендерить view с данными
     */
    public static function render(string $view, array $data = []): void
    {
        // Извлекаем переменные из массива $data
        extract($data);

        // Определяем путь к view файлу
        $viewPath = APP_PATH . '/Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            die("View {$view} not found at {$viewPath}");
        }

        // Подключаем view файл
        require $viewPath;
    }

    /**
     * Отрендерить view и вернуть как строку
     */
    public static function make(string $view, array $data = []): string
    {
        ob_start();
        self::render($view, $data);
        return ob_get_clean();
    }
}
