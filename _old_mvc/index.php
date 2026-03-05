<?php
/**
 * Front Controller
 * Все HTTP-запросы проходят через этот файл
 */

// Определяем константы путей
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('CORE_PATH', BASE_PATH . '/core');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Включаем обработку ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключаем bootstrap (автозагрузка классов и инициализация)
require_once CORE_PATH . '/bootstrap.php';

use Core\Router;
use Core\Request;
use Core\Session;

// Запускаем сессии
Session::start();

// Создаем объект запроса
$request = new Request();

// Загружаем маршруты
$routes = require_once CONFIG_PATH . '/routes.php';

// Создаем роутер и обрабатываем запрос
try {
    $router = new Router($routes);
    $router->dispatch($request);
} catch (Exception $e) {
    // Обработка ошибок
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
