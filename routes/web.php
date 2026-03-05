<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\CaseSearchController;
use App\Http\Controllers\PsychologistController;
use App\Http\Controllers\Admin\IntervisionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController as AdminUsers;
use Illuminate\Support\Facades\Route;

// Публичные страницы
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');

// Публичный каталог психологов
Route::get('/psychologists', [PsychologistController::class, 'index'])->name('psychologists.index');
Route::get('/psychologists/{id}', [PsychologistController::class, 'show'])->name('psychologists.show');

// Авторизованные маршруты
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== КЛИЕНТ ====================
    Route::middleware(['role:CLIENT'])->prefix('client')->name('client.')->group(function () {
        Route::get('/cases', [CaseController::class, 'index'])->name('cases.index');
        Route::get('/cases/create', [CaseController::class, 'create'])->name('cases.create');
        Route::post('/cases', [CaseController::class, 'store'])->name('cases.store');
        Route::get('/cases/{id}', [CaseController::class, 'show'])->name('cases.show');
        Route::post('/cases/{id}/close', [CaseController::class, 'close'])->name('cases.close');
        Route::post('/cases/{caseId}/accept/{responseId}', [CaseController::class, 'acceptResponse'])->name('cases.accept-response');
    });

    // ==================== ПСИХОЛОГ ====================
    Route::middleware(['role:PSYCHOLOGIST'])->prefix('psychologist')->name('psychologist.')->group(function () {
        Route::get('/cases', [CaseSearchController::class, 'index'])->name('cases.index');
        Route::get('/cases/{id}', [CaseSearchController::class, 'show'])->name('cases.show');
        Route::post('/cases/{id}/respond', [CaseSearchController::class, 'respond'])->name('cases.respond');
        Route::get('/intervisions', [DashboardController::class, 'intervisionStatus'])->name('intervisions');
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
