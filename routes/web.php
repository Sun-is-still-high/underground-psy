<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\CaseSearchController;
use App\Http\Controllers\PsychologistController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\Admin\IntervisionController;
use App\Http\Controllers\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\Triads\TaskController as TriadTaskController;
use App\Http\Controllers\Triads\SlotController;
use App\Http\Controllers\Admin\VerificationController as AdminVerification;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController as AdminUsers;
use Illuminate\Support\Facades\Route;

// Публичные страницы
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');

// Публичный каталог психологов
Route::get('/psychologists', [PsychologistController::class, 'index'])->name('psychologists.index');
Route::get('/psychologists/{id}', [PsychologistController::class, 'show'])->name('psychologists.show');

// Публичные мероприятия
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Публичные вопросы и ответы
Route::get('/questions', [QuestionController::class, 'publicIndex'])->name('questions.index');
Route::get('/ask', [QuestionController::class, 'askForm'])->name('questions.ask');
Route::post('/ask', [QuestionController::class, 'askSubmit'])->name('questions.ask.submit');

// Авторизованные маршруты
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Настройки (все авторизованные роли)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/timezone', [SettingsController::class, 'saveTimezone'])->name('settings.timezone');
    Route::post('/settings/gender', [SettingsController::class, 'saveGender'])->name('settings.gender');

    // ==================== КЛИЕНТ ====================
    Route::middleware(['role:CLIENT'])->prefix('client')->name('client.')->group(function () {
        Route::get('/cases', [CaseController::class, 'index'])->name('cases.index');
        Route::get('/cases/create', [CaseController::class, 'create'])->name('cases.create');
        Route::post('/cases', [CaseController::class, 'store'])->name('cases.store');
        Route::get('/cases/{id}', [CaseController::class, 'show'])->name('cases.show');
        Route::post('/cases/{id}/close', [CaseController::class, 'close'])->name('cases.close');
        Route::post('/cases/{caseId}/accept/{responseId}', [CaseController::class, 'acceptResponse'])->name('cases.accept-response');
    });

    // ==================== ТРОЙКИ ====================
    Route::prefix('triads')->name('triads.')->group(function () {
        // Банк заданий — просмотр одобренных (все авторизованные)
        Route::get('/tasks', [TriadTaskController::class, 'index'])->name('tasks.index');

        // Слоты — лента и детали доступны всем авторизованным
        Route::get('/slots', [SlotController::class, 'index'])->name('slots.index');
        Route::get('/slots/{slot}', [SlotController::class, 'show'])->name('slots.show');

        // Мои тройки (участие + созданные)
        Route::get('/my-slots', [SlotController::class, 'mySlots'])->name('my-slots');

        // Только психологи
        Route::middleware(['role:PSYCHOLOGIST'])->group(function () {
            // Задания: предложить, мои задания, редактировать отклонённое
            Route::get('/tasks/propose', [TriadTaskController::class, 'create'])->name('tasks.create');
            Route::post('/tasks', [TriadTaskController::class, 'store'])->name('tasks.store');
            Route::get('/tasks/my', [TriadTaskController::class, 'my'])->name('tasks.my');
            Route::get('/tasks/{task}/edit', [TriadTaskController::class, 'edit'])->name('tasks.edit');
            Route::put('/tasks/{task}', [TriadTaskController::class, 'update'])->name('tasks.update');

            // Слоты: создание, запись, отписка, отмена
            Route::get('/slots/create', [SlotController::class, 'create'])->name('slots.create');
            Route::post('/slots', [SlotController::class, 'store'])->name('slots.store');
            Route::delete('/slots/{slot}', [SlotController::class, 'cancel'])->name('slots.cancel');
            Route::post('/slots/{slot}/join', [SlotController::class, 'join'])->name('slots.join');
            Route::delete('/slots/{slot}/leave', [SlotController::class, 'leave'])->name('slots.leave');
        });
    });

    // ==================== ПСИХОЛОГ ====================
    Route::middleware(['role:PSYCHOLOGIST'])->prefix('psychologist')->name('psychologist.')->group(function () {
        Route::get('/cases', [CaseSearchController::class, 'index'])->name('cases.index');
        Route::get('/cases/{id}', [CaseSearchController::class, 'show'])->name('cases.show');
        Route::post('/cases/{id}/respond', [CaseSearchController::class, 'respond'])->name('cases.respond');
        Route::get('/intervisions', [DashboardController::class, 'intervisionStatus'])->name('intervisions');
        Route::get('/questions', [QuestionController::class, 'psychologistIndex'])->name('questions');
        Route::post('/questions/{question}/answer', [QuestionController::class, 'answer'])->name('questions.answer');
        Route::get('/events', [EventController::class, 'myEvents'])->name('events');
        Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/profile/edit', [PsychologistController::class, 'editProfile'])->name('profile.edit');
        Route::post('/profile/update', [PsychologistController::class, 'updateProfile'])->name('profile.update');
    });

    // ==================== ADMIN ====================
    Route::middleware(['role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
        // Главная панель и пользователи
        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminUsers::class, 'index'])->name('users.index');
        Route::post('/users/{user}/block', [AdminUsers::class, 'block'])->name('users.block');
        Route::post('/users/{user}/unblock', [AdminUsers::class, 'unblock'])->name('users.unblock');

        // Верификация дипломов
        Route::get('/verification', [AdminVerification::class, 'index'])->name('verification.index');
        Route::post('/verification/{profile}/approve', [AdminVerification::class, 'approve'])->name('verification.approve');
        Route::post('/verification/{profile}/reject', [AdminVerification::class, 'reject'])->name('verification.reject');

        // Модерация заданий для троек
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [AdminTaskController::class, 'index'])->name('index');
            Route::get('/{task}', [AdminTaskController::class, 'show'])->name('show');
            Route::post('/{task}/approve', [AdminTaskController::class, 'approve'])->name('approve');
            Route::post('/{task}/reject', [AdminTaskController::class, 'reject'])->name('reject');
            Route::put('/{task}', [AdminTaskController::class, 'update'])->name('update');
        });

        // Интервизии
        Route::prefix('intervision')->name('intervision.')->group(function () {
            Route::get('/groups', [IntervisionController::class, 'groups'])->name('groups');
            Route::get('/groups/create', [IntervisionController::class, 'createGroup'])->name('groups.create');
            Route::post('/groups', [IntervisionController::class, 'storeGroup'])->name('groups.store');
            Route::get('/groups/{id}', [IntervisionController::class, 'showGroup'])->name('groups.show');
            Route::get('/groups/{id}/edit', [IntervisionController::class, 'editGroup'])->name('groups.edit');
            Route::put('/groups/{id}', [IntervisionController::class, 'updateGroup'])->name('groups.update');
            Route::post('/groups/{id}/deactivate', [IntervisionController::class, 'deleteGroup'])->name('groups.delete');

            Route::post('/groups/{id}/participants', [IntervisionController::class, 'addParticipant'])->name('participants.add');
            Route::post('/groups/{groupId}/participants/{psychologistId}/remove', [IntervisionController::class, 'removeParticipant'])->name('participants.remove');

            Route::get('/groups/{id}/sessions/create', [IntervisionController::class, 'createSession'])->name('sessions.create');
            Route::post('/groups/{id}/sessions', [IntervisionController::class, 'storeSession'])->name('sessions.store');
            Route::get('/sessions/{id}', [IntervisionController::class, 'showSession'])->name('sessions.show');
            Route::post('/sessions/{id}/status', [IntervisionController::class, 'changeSessionStatus'])->name('sessions.status');
            Route::get('/sessions/{id}/attendance', [IntervisionController::class, 'attendanceForm'])->name('sessions.attendance');
            Route::post('/sessions/{id}/attendance', [IntervisionController::class, 'saveAttendance'])->name('sessions.attendance.save');
        });
    });
});

require __DIR__.'/auth.php';
