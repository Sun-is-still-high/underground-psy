<?php
$title = htmlspecialchars($group['name']) . ' - Группа интервизий';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1><?= htmlspecialchars($group['name']) ?></h1>
        <div class="header-actions">
            <a href="/admin/intervision/groups/<?= $group['id'] ?>/edit" class="btn btn-outline">Редактировать</a>
            <a href="/admin/intervision/groups" class="btn btn-outline">Назад к списку</a>
        </div>
    </div>

    <?php if ($group['description']): ?>
        <div class="description-box">
            <p><?= nl2br(htmlspecialchars($group['description'])) ?></p>
        </div>
    <?php endif; ?>

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['participants_count'] ?>/<?= $group['max_participants'] ?></div>
            <div class="stat-label">Участников</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_sessions'] ?></div>
            <div class="stat-label">Всего сессий</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['completed_sessions'] ?></div>
            <div class="stat-label">Завершено</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['upcoming_sessions'] ?></div>
            <div class="stat-label">Предстоит</div>
        </div>
    </div>

    <!-- Участники -->
    <div class="section">
        <div class="section-header">
            <h2>Участники группы</h2>
        </div>

        <?php if (!empty($group['participants'])): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>В группе с</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($group['participants'] as $participant): ?>
                    <tr>
                        <td><?= htmlspecialchars($participant['name']) ?></td>
                        <td><?= htmlspecialchars($participant['email']) ?></td>
                        <td><?= date('d.m.Y', strtotime($participant['joined_at'])) ?></td>
                        <td>
                            <form action="/admin/intervision/groups/<?= $group['id'] ?>/participants/<?= $participant['psychologist_id'] ?>/remove" method="POST" style="display:inline;">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить участника?')">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-message">В группе пока нет участников.</p>
        <?php endif; ?>

        <?php if (!empty($availablePsychologists)): ?>
            <div class="add-participant-form">
                <h3>Добавить психолога</h3>
                <form action="/admin/intervision/groups/<?= $group['id'] ?>/participants" method="POST" class="inline-form">
                    <select name="psychologist_id" required>
                        <option value="">Выберите психолога</option>
                        <?php foreach ($availablePsychologists as $psy): ?>
                            <option value="<?= $psy['id'] ?>"><?= htmlspecialchars($psy['name']) ?> (<?= htmlspecialchars($psy['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Сессии -->
    <div class="section">
        <div class="section-header">
            <h2>Сессии интервизий</h2>
            <a href="/admin/intervision/groups/<?= $group['id'] ?>/sessions/create" class="btn btn-primary">Запланировать сессию</a>
        </div>

        <?php if (!empty($sessions)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Тема</th>
                        <th>Дата и время</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                    <?php
                        $statusLabels = [
                            'SCHEDULED' => '<span class="badge badge-info">Запланирована</span>',
                            'IN_PROGRESS' => '<span class="badge badge-warning">В процессе</span>',
                            'COMPLETED' => '<span class="badge badge-success">Завершена</span>',
                            'CANCELLED' => '<span class="badge badge-danger">Отменена</span>',
                        ];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($session['topic']) ?></strong></td>
                        <td><?= date('d.m.Y H:i', strtotime($session['scheduled_at'])) ?></td>
                        <td><?= $statusLabels[$session['status']] ?? $session['status'] ?></td>
                        <td>
                            <a href="/admin/intervision/sessions/<?= $session['id'] ?>" class="btn btn-sm">Открыть</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-message">Сессий пока нет. Запланируйте первую сессию.</p>
        <?php endif; ?>
    </div>

    <!-- Удаление группы -->
    <div class="section danger-zone">
        <h2>Опасная зона</h2>
        <form action="/admin/intervision/groups/<?= $group['id'] ?>/delete" method="POST">
            <p>Деактивация группы скроет её из списка. Данные сохранятся.</p>
            <button type="submit" class="btn btn-danger" onclick="return confirm('Деактивировать группу?')">Деактивировать группу</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../../layouts/main.php';
?>
