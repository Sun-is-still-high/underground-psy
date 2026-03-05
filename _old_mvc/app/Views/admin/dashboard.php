<?php
$title = 'Админ-панель — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Админ-панель</h1>
        <p class="page-subtitle">Управление платформой Underground Psy</p>
    </div>

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $byRole['CLIENT'] ?></div>
            <div class="stat-label">Клиентов</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $byRole['PSYCHOLOGIST'] ?></div>
            <div class="stat-label">Психологов</div>
        </div>
        <div class="stat-card stat-card--highlight">
            <div class="stat-value">+<?= $newUsers ?></div>
            <div class="stat-label">Новых за 30 дней</div>
        </div>
        <div class="stat-card <?= $unansweredQuestions > 0 ? 'stat-card--warning' : '' ?>">
            <div class="stat-value"><?= $unansweredQuestions ?></div>
            <div class="stat-label">Вопросов без ответа</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $upcomingEvents ?> / <?= $totalEvents ?></div>
            <div class="stat-label">Предстоящих / всего мероприятий</div>
        </div>
        <?php if ($blockedUsers > 0): ?>
        <div class="stat-card stat-card--warning">
            <div class="stat-value"><?= $blockedUsers ?></div>
            <div class="stat-label">Заблокировано</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Разделы управления -->
    <div class="admin-sections">
        <h2>Разделы</h2>
        <div class="admin-nav-grid">
            <a href="/admin/users" class="admin-nav-card">
                <div class="admin-nav-card__title">Пользователи</div>
                <div class="admin-nav-card__desc">Список, блокировка, поиск</div>
            </a>
            <a href="/admin/verification" class="admin-nav-card <?= $pendingVerification > 0 ? 'admin-nav-card--badge' : '' ?>">
                <div class="admin-nav-card__title">
                    Верификация дипломов
                    <?php if ($pendingVerification > 0): ?>
                        <span class="badge badge-warning"><?= $pendingVerification ?></span>
                    <?php endif; ?>
                </div>
                <div class="admin-nav-card__desc">Проверка документов психологов</div>
            </a>
            <a href="/admin/intervision/groups" class="admin-nav-card">
                <div class="admin-nav-card__title">Интервизии</div>
                <div class="admin-nav-card__desc">Группы и сессии</div>
            </a>
            <a href="/questions" class="admin-nav-card">
                <div class="admin-nav-card__title">Вопросы</div>
                <div class="admin-nav-card__desc">Публичные вопросы пользователей</div>
            </a>
            <a href="/events" class="admin-nav-card">
                <div class="admin-nav-card__title">Мероприятия</div>
                <div class="admin-nav-card__desc">Список всех мероприятий</div>
            </a>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 40px;
}
.stat-card {
    background: var(--bg-card, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}
.stat-card--highlight { border-color: #6366f1; }
.stat-card--warning   { border-color: #f59e0b; }
.stat-value { font-size: 2rem; font-weight: 700; line-height: 1; margin-bottom: 6px; }
.stat-label { font-size: 0.85rem; color: #6b7280; }

.admin-nav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    margin-top: 16px;
}
.admin-nav-card {
    display: block;
    background: var(--bg-card, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 8px;
    padding: 20px;
    text-decoration: none;
    color: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.admin-nav-card:hover { border-color: #6366f1; box-shadow: 0 2px 8px rgba(99,102,241,.12); }
.admin-nav-card__title { font-weight: 600; margin-bottom: 4px; }
.admin-nav-card__desc  { font-size: 0.85rem; color: #6b7280; }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
