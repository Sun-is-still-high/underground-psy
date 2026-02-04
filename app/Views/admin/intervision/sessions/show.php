<?php
$title = htmlspecialchars($session['topic']) . ' - Сессия интервизии';
$statusLabels = [
    'SCHEDULED' => '<span class="badge badge-info">Запланирована</span>',
    'IN_PROGRESS' => '<span class="badge badge-warning">В процессе</span>',
    'COMPLETED' => '<span class="badge badge-success">Завершена</span>',
    'CANCELLED' => '<span class="badge badge-danger">Отменена</span>',
];
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1><?= htmlspecialchars($session['topic']) ?></h1>
        <div class="header-actions">
            <a href="/admin/intervision/groups/<?= $group['id'] ?>" class="btn btn-outline">Назад к группе</a>
        </div>
    </div>

    <div class="session-meta">
        <p><strong>Группа:</strong> <?= htmlspecialchars($group['name']) ?></p>
        <p><strong>Статус:</strong> <?= $statusLabels[$session['status']] ?? $session['status'] ?></p>
        <p><strong>Дата и время:</strong> <?= date('d.m.Y H:i', strtotime($session['scheduled_at'])) ?></p>
        <p><strong>Длительность:</strong> <?= $session['duration_minutes'] ?> мин</p>
        <?php if ($session['meeting_link']): ?>
            <p><strong>Ссылка:</strong> <a href="<?= htmlspecialchars($session['meeting_link']) ?>" target="_blank"><?= htmlspecialchars($session['meeting_link']) ?></a></p>
        <?php endif; ?>
    </div>

    <?php if ($session['description']): ?>
        <div class="description-box">
            <h3>Описание</h3>
            <p><?= nl2br(htmlspecialchars($session['description'])) ?></p>
        </div>
    <?php endif; ?>

    <?php if ($session['status'] === 'CANCELLED' && $session['cancelled_reason']): ?>
        <div class="alert alert-warning">
            <strong>Причина отмены:</strong> <?= htmlspecialchars($session['cancelled_reason']) ?>
        </div>
    <?php endif; ?>

    <!-- Посещаемость -->
    <div class="section">
        <div class="section-header">
            <h2>Посещаемость</h2>
            <?php if ($session['status'] !== 'CANCELLED'): ?>
                <a href="/admin/intervision/sessions/<?= $session['id'] ?>/attendance" class="btn btn-primary">Отметить посещаемость</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($session['attendance'])): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Участник</th>
                        <th>Email</th>
                        <th>Присутствовал</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($session['attendance'] as $record): ?>
                    <tr>
                        <td><?= htmlspecialchars($record['name']) ?></td>
                        <td><?= htmlspecialchars($record['email']) ?></td>
                        <td>
                            <?php if ($record['attended']): ?>
                                <span class="badge badge-success">Да</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Нет</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-message">Данные о посещаемости ещё не внесены.</p>
        <?php endif; ?>
    </div>

    <!-- Управление статусом -->
    <?php if ($session['status'] === 'SCHEDULED'): ?>
    <div class="section">
        <h2>Управление сессией</h2>
        <div class="button-group">
            <form action="/admin/intervision/sessions/<?= $session['id'] ?>/status" method="POST" style="display:inline;">
                <input type="hidden" name="status" value="CANCELLED">
                <input type="text" name="reason" placeholder="Причина отмены" style="width:200px;">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Отменить сессию?')">Отменить сессию</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../../layouts/main.php';
?>
