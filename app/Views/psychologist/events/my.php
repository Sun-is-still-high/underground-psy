<?php
$title = 'Мои мероприятия — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Мои мероприятия</h1>
        <a href="/psychologist/events/create" class="btn btn-primary">+ Создать</a>
    </div>

    <?php if (empty($events)): ?>
        <div class="empty-state">
            <p>У вас пока нет мероприятий.</p>
            <a href="/psychologist/events/create" class="btn btn-primary" style="margin-top:1rem;">Создать первое</a>
        </div>
    <?php else: ?>
        <div class="events-table">
            <?php foreach ($events as $event): ?>
                <div class="event-row">
                    <div class="event-row-info">
                        <strong><?= htmlspecialchars($event['title']) ?></strong>
                        <span class="text-muted"><?= htmlspecialchars($types[$event['event_type']] ?? $event['event_type']) ?></span>
                    </div>
                    <div class="event-row-date">
                        <?= date('d.m.Y H:i', strtotime($event['scheduled_at'])) ?>
                    </div>
                    <div class="event-row-status">
                        <span class="badge <?= $event['status'] === 'ACTIVE' ? 'badge-success' : 'badge-outline' ?>">
                            <?= ['ACTIVE' => 'Активно', 'CANCELLED' => 'Отменено', 'COMPLETED' => 'Завершено'][$event['status']] ?? $event['status'] ?>
                        </span>
                    </div>
                    <div class="event-row-actions">
                        <a href="/events/<?= $event['id'] ?>" class="btn btn-outline btn-sm">Посмотреть</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>
