<?php
/**
 * Определение маршрутов приложения
 */

return [
    // Главная страница
    ['method' => 'GET', 'uri' => '/', 'action' => 'HomeController@index'],
    ['method' => 'GET', 'uri' => '/about', 'action' => 'HomeController@about'],

    // Аутентификация - страницы
    ['method' => 'GET', 'uri' => '/login', 'action' => 'AuthController@loginPage'],
    ['method' => 'GET', 'uri' => '/register', 'action' => 'AuthController@registerPage'],

    // Аутентификация - действия
    ['method' => 'POST', 'uri' => '/login', 'action' => 'AuthController@login'],
    ['method' => 'POST', 'uri' => '/register', 'action' => 'AuthController@register'],
    ['method' => 'POST', 'uri' => '/logout', 'action' => 'AuthController@logout'],

    // Защищенные маршруты
    ['method' => 'GET', 'uri' => '/dashboard', 'action' => 'DashboardController@index'],

    // ==================== ИНТЕРВИЗИИ (Админ) ====================

    // Группы
    ['method' => 'GET',  'uri' => '/admin/intervision/groups', 'action' => 'Admin\IntervisionController@groups'],
    ['method' => 'GET',  'uri' => '/admin/intervision/groups/create', 'action' => 'Admin\IntervisionController@createGroup'],
    ['method' => 'POST', 'uri' => '/admin/intervision/groups', 'action' => 'Admin\IntervisionController@storeGroup'],
    ['method' => 'GET',  'uri' => '/admin/intervision/groups/{id}', 'action' => 'Admin\IntervisionController@showGroup'],
    ['method' => 'GET',  'uri' => '/admin/intervision/groups/{id}/edit', 'action' => 'Admin\IntervisionController@editGroup'],
    ['method' => 'POST', 'uri' => '/admin/intervision/groups/{id}', 'action' => 'Admin\IntervisionController@updateGroup'],
    ['method' => 'POST', 'uri' => '/admin/intervision/groups/{id}/delete', 'action' => 'Admin\IntervisionController@deleteGroup'],

    // Участники групп
    ['method' => 'POST', 'uri' => '/admin/intervision/groups/{id}/participants', 'action' => 'Admin\IntervisionController@addParticipant'],
    ['method' => 'POST', 'uri' => '/admin/intervision/groups/{groupId}/participants/{odontologyIds}/remove', 'action' => 'Admin\IntervisionController@removeParticipant'],

    // Сессии
    ['method' => 'GET',  'uri' => '/admin/intervision/groups/{id}/sessions/create', 'action' => 'Admin\IntervisionController@createSession'],
    ['method' => 'POST', 'uri' => '/admin/intervision/groups/{id}/sessions', 'action' => 'Admin\IntervisionController@storeSession'],
    ['method' => 'GET',  'uri' => '/admin/intervision/sessions/{id}', 'action' => 'Admin\IntervisionController@showSession'],
    ['method' => 'POST', 'uri' => '/admin/intervision/sessions/{id}/status', 'action' => 'Admin\IntervisionController@changeSessionStatus'],

    // Посещаемость
    ['method' => 'GET',  'uri' => '/admin/intervision/sessions/{id}/attendance', 'action' => 'Admin\IntervisionController@attendanceForm'],
    ['method' => 'POST', 'uri' => '/admin/intervision/sessions/{id}/attendance', 'action' => 'Admin\IntervisionController@saveAttendance'],

    // ==================== ИНТЕРВИЗИИ (Психолог) ====================
    ['method' => 'GET', 'uri' => '/psychologist/intervisions', 'action' => 'DashboardController@intervisionStatus'],
];
