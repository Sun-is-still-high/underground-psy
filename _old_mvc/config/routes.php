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

    // ==================== АДМИН-ПАНЕЛЬ ====================
    ['method' => 'GET',  'uri' => '/admin',                      'action' => 'Admin\DashboardController@index'],
    ['method' => 'GET',  'uri' => '/admin/users',                'action' => 'Admin\UserController@index'],
    ['method' => 'POST', 'uri' => '/admin/users/{id}/block',     'action' => 'Admin\UserController@block'],
    ['method' => 'POST', 'uri' => '/admin/users/{id}/unblock',   'action' => 'Admin\UserController@unblock'],

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

    // ==================== КЕЙСЫ (Клиент) ====================
    ['method' => 'GET',  'uri' => '/client/cases', 'action' => 'CaseController@index'],
    ['method' => 'GET',  'uri' => '/client/cases/create', 'action' => 'CaseController@create'],
    ['method' => 'POST', 'uri' => '/client/cases', 'action' => 'CaseController@store'],
    ['method' => 'GET',  'uri' => '/client/cases/{id}', 'action' => 'CaseController@show'],
    ['method' => 'POST', 'uri' => '/client/cases/{id}/close', 'action' => 'CaseController@close'],
    ['method' => 'POST', 'uri' => '/client/cases/{caseId}/accept/{responseId}', 'action' => 'CaseController@acceptResponse'],

    // ==================== ПОИСК КЕЙСОВ (Психолог) ====================
    ['method' => 'GET',  'uri' => '/psychologist/cases', 'action' => 'CaseSearchController@index'],
    ['method' => 'GET',  'uri' => '/psychologist/cases/{id}', 'action' => 'CaseSearchController@show'],
    ['method' => 'POST', 'uri' => '/psychologist/cases/{id}/respond', 'action' => 'CaseSearchController@respond'],

    // ==================== ПРОФИЛИ ПСИХОЛОГОВ ====================

    // Редактирование профиля (должно быть ДО /psychologists/{id})
    ['method' => 'GET',  'uri' => '/psychologist/profile/edit', 'action' => 'PsychologistController@editProfile'],
    ['method' => 'POST', 'uri' => '/psychologist/profile/update', 'action' => 'PsychologistController@updateProfile'],
    ['method' => 'POST', 'uri' => '/psychologist/diploma/upload', 'action' => 'PsychologistController@uploadDiploma'],
    ['method' => 'POST', 'uri' => '/psychologist/profile/confirm-price', 'action' => 'PsychologistController@confirmPrice'],

    // Публичный каталог
    ['method' => 'GET',  'uri' => '/psychologists', 'action' => 'PsychologistController@index'],
    ['method' => 'GET',  'uri' => '/psychologists/{id}', 'action' => 'PsychologistController@show'],

    // ==================== НАСТРОЙКИ ====================
    ['method' => 'GET',  'uri' => '/settings', 'action' => 'SettingsController@index'],
    ['method' => 'POST', 'uri' => '/settings/timezone', 'action' => 'SettingsController@saveTimezone'],

    // ==================== ПУБЛИЧНЫЕ ВОПРОСЫ ====================
    ['method' => 'GET',  'uri' => '/ask', 'action' => 'QuestionController@askForm'],
    ['method' => 'POST', 'uri' => '/ask', 'action' => 'QuestionController@askSubmit'],
    ['method' => 'GET',  'uri' => '/questions', 'action' => 'QuestionController@publicIndex'],
    ['method' => 'GET',  'uri' => '/psychologist/questions', 'action' => 'QuestionController@psychologistIndex'],
    ['method' => 'POST', 'uri' => '/psychologist/questions/{id}/answer', 'action' => 'QuestionController@answer'],

    // ==================== МЕРОПРИЯТИЯ ====================
    ['method' => 'GET',  'uri' => '/events', 'action' => 'EventController@index'],
    ['method' => 'GET',  'uri' => '/events/{id}', 'action' => 'EventController@show'],
    ['method' => 'GET',  'uri' => '/psychologist/events', 'action' => 'EventController@myEvents'],
    ['method' => 'GET',  'uri' => '/psychologist/events/create', 'action' => 'EventController@create'],
    ['method' => 'POST', 'uri' => '/psychologist/events', 'action' => 'EventController@store'],

    // ==================== ВЕРИФИКАЦИЯ ДИПЛОМОВ (Админ) ====================
    ['method' => 'GET',  'uri' => '/admin/verification', 'action' => 'Admin\VerificationController@index'],
    ['method' => 'POST', 'uri' => '/admin/verification/{id}/approve', 'action' => 'Admin\VerificationController@approve'],
    ['method' => 'POST', 'uri' => '/admin/verification/{id}/reject', 'action' => 'Admin\VerificationController@reject'],

    // ==================== БИЗНЕС-СТРАНИЦЫ ====================
    ['method' => 'GET', 'uri' => '/business', 'action' => 'HomeController@business'],
    ['method' => 'GET', 'uri' => '/medical',  'action' => 'HomeController@medical'],
];
