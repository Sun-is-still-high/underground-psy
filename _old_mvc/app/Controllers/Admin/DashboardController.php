<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Database;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        // Счётчики пользователей по ролям
        $roleCounts = Database::query(
            "SELECT role, COUNT(*) as cnt FROM users GROUP BY role"
        );
        $byRole = ['CLIENT' => 0, 'PSYCHOLOGIST' => 0, 'ADMIN' => 0];
        foreach ($roleCounts as $row) {
            $byRole[$row['role']] = (int) $row['cnt'];
        }

        // Новые пользователи за 30 дней
        $newUsers = (int) Database::queryOne(
            "SELECT COUNT(*) as cnt FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        )['cnt'];

        // Вопросы без ответа
        $unansweredQuestions = (int) Database::queryOne(
            "SELECT COUNT(*) as cnt FROM public_questions pq
             WHERE NOT EXISTS (SELECT 1 FROM public_answers pa WHERE pa.question_id = pq.id)"
        )['cnt'];

        // Всего мероприятий / предстоящих
        $totalEvents = (int) Database::queryOne(
            "SELECT COUNT(*) as cnt FROM events"
        )['cnt'];
        $upcomingEvents = (int) Database::queryOne(
            "SELECT COUNT(*) as cnt FROM events WHERE starts_at > NOW()"
        )['cnt'];

        // Психологи ожидают верификации диплома
        $pendingVerification = (int) Database::queryOne(
            "SELECT COUNT(*) as cnt FROM psychologist_profiles
             WHERE diploma_scan_url IS NOT NULL AND (diploma_verified = 0 OR diploma_verified IS NULL)"
        )['cnt'];

        // Заблокированные пользователи
        $blockedUsers = (int) Database::queryOne(
            "SELECT COUNT(*) as cnt FROM users WHERE is_blocked = 1"
        )['cnt'];

        $this->view('admin/dashboard', [
            'title'               => 'Админ-панель',
            'byRole'              => $byRole,
            'newUsers'            => $newUsers,
            'unansweredQuestions' => $unansweredQuestions,
            'totalEvents'         => $totalEvents,
            'upcomingEvents'      => $upcomingEvents,
            'pendingVerification' => $pendingVerification,
            'blockedUsers'        => $blockedUsers,
        ]);
    }
}
