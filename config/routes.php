<?php
/**
 * Определение маршрутов приложения
 */

return [
    // Главная страница
    ['method' => 'GET', 'uri' => '/', 'action' => 'HomeController@index'],

    // Аутентификация - страницы
    ['method' => 'GET', 'uri' => '/login', 'action' => 'AuthController@loginPage'],
    ['method' => 'GET', 'uri' => '/register', 'action' => 'AuthController@registerPage'],

    // Аутентификация - действия
    ['method' => 'POST', 'uri' => '/login', 'action' => 'AuthController@login'],
    ['method' => 'POST', 'uri' => '/register', 'action' => 'AuthController@register'],
    ['method' => 'POST', 'uri' => '/logout', 'action' => 'AuthController@logout'],

    // Защищенные маршруты
    ['method' => 'GET', 'uri' => '/dashboard', 'action' => 'DashboardController@index'],
];
