<?php
namespace Core;

/**
 * Router - маршрутизация запросов к контроллерам
 */
class Router
{
    private array $routes = [];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Обработать запрос и вызвать соответствующий контроллер
     */
    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->uri();

        // Ищем совпадение маршрута
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchUri($route['uri'], $uri)) {
                $this->callAction($route['action'], $uri, $route['uri']);
                return;
            }
        }

        // 404 если маршрут не найден
        $this->notFound();
    }

    /**
     * Проверить, совпадает ли URI с паттерном маршрута
     */
    private function matchUri(string $pattern, string $uri): bool
    {
        // Преобразуем {id} в регулярное выражение
        $pattern = preg_replace('/\{[a-zA-Z]+\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        return (bool) preg_match($pattern, $uri);
    }

    /**
     * Вызвать метод контроллера
     */
    private function callAction(string $action, string $uri, string $pattern): void
    {
        // Парсим action: 'AuthController@login'
        [$controllerName, $method] = explode('@', $action);

        // Полное имя класса контроллера
        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            die("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            die("Method {$method} not found in {$controllerClass}");
        }

        // Извлекаем параметры из URI (например, {id})
        $params = $this->extractParams($pattern, $uri);

        // Вызываем метод контроллера с параметрами
        call_user_func_array([$controller, $method], $params);
    }

    /**
     * Извлечь параметры из URI
     */
    private function extractParams(string $pattern, string $uri): array
    {
        // Преобразуем {id} в capturing group
        $regex = preg_replace('/\{[a-zA-Z]+\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        preg_match($regex, $uri, $matches);

        // Убираем первый элемент (полное совпадение)
        array_shift($matches);

        return $matches;
    }

    /**
     * Обработка 404
     */
    private function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
        echo '<p>The page you are looking for does not exist.</p>';
        exit;
    }
}
