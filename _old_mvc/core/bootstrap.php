<?php
/**
 * Bootstrap - инициализация приложения и автозагрузка классов
 */

// PSR-4 автозагрузка классов
spl_autoload_register(function ($class) {
    // Заменяем namespace separator на directory separator
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    // Определяем пути для разных namespace
    $paths = [
        BASE_PATH . '/' . $class . '.php',           // Core\Router -> core/Router.php
        BASE_PATH . '/' . strtolower($class) . '.php', // Для lowercase папок
    ];

    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Загружаем конфигурацию приложения
$appConfig = require_once CONFIG_PATH . '/app.php';

// Устанавливаем часовой пояс
date_default_timezone_set($appConfig['timezone'] ?? 'UTC');
