<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Счётчики пользователей по ролям
        $roleCounts = User::selectRaw('role, count(*) as cnt')
            ->groupBy('role')
            ->pluck('cnt', 'role')
            ->toArray();
        $byRole = array_merge(['CLIENT' => 0, 'PSYCHOLOGIST' => 0, 'ADMIN' => 0], $roleCounts);

        // Новые пользователи за 30 дней
        $newUsers = User::where('created_at', '>=', now()->subDays(30))->count();

        // Вопросы без ответа
        $unansweredQuestions = DB::table('public_questions')
            ->where('status', 'PENDING')
            ->count();

        // Мероприятия
        $totalEvents    = DB::table('events')->count();
        $upcomingEvents = DB::table('events')->where('scheduled_at', '>', now())->count();

        // Психологи ожидают верификации диплома
        $pendingVerification = DB::table('psychologist_profiles')
            ->whereNotNull('diploma_scan_url')
            ->where('diploma_verified', false)
            ->count();

        // Заблокированные пользователи
        $blockedUsers = User::where('is_blocked', true)->count();

        // Задания для троек на модерации
        $pendingTasks = Task::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'byRole',
            'newUsers',
            'unansweredQuestions',
            'totalEvents',
            'upcomingEvents',
            'pendingVerification',
            'blockedUsers',
            'pendingTasks',
        ));
    }
}
