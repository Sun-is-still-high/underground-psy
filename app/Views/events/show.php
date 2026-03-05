<?php
$title = htmlspecialchars($event['title']) . ' — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="event-page">
        <div class="event-page-header">
            <span class="event-type-badge"><?= htmlspecialchars($types[$event['event_type']] ?? $event['event_type']) ?></span>
            <h1><?= htmlspecialchars($event['title']) ?></h1>

            <div class="event-meta">
                <span>📅 <?= date('d.m.Y H:i', strtotime($event['scheduled_at'])) ?></span>
                <span>⏱ <?= $event['duration_minutes'] ?> мин.</span>
                <span class="badge <?= $event['format'] === 'ONLINE' ? 'badge-online' : 'badge-offline' ?>">
                    <?= $formats[$event['format']] ?? $event['format'] ?>
                </span>
                <?php if (!empty($event['city'])): ?>
                    <span>📍 <?= htmlspecialchars($event['city']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="event-page-content">
            <div class="event-organizer">
                <strong>Организатор:</strong>
                <?php if ($event['organizer_profile_id']): ?>
                    <a href="/psychologists/<?= $event['organizer_profile_id'] ?>">
                        <?= htmlspecialchars($event['organizer_name']) ?>
                    </a>
                <?php else: ?>
                    <?= htmlspecialchars($event['organizer_name']) ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($event['description'])): ?>
                <div class="profile-section">
                    <h2>Описание</h2>
                    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="event-details-grid">
                <div class="event-detail-item">
                    <span class="detail-label">Стоимость</span>
                    <span class="detail-value">
                        <?= $event['price'] ? number_format($event['price'], 0, '', ' ') . ' ₽' : 'Бесплатно' ?>
                    </span>
                </div>
                <?php if ($event['max_participants']): ?>
                    <div class="event-detail-item">
                        <span class="detail-label">Мест</span>
                        <span class="detail-value"><?= $event['max_participants'] ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($event['meeting_link']) && $event['format'] === 'ONLINE'): ?>
                    <div class="event-detail-item">
                        <span class="detail-label">Ссылка</span>
                        <span class="detail-value">
                            <a href="<?= htmlspecialchars($event['meeting_link']) ?>" target="_blank" rel="noopener">Присоединиться</a>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-actions">
            <a href="/events" class="btn btn-outline">← Все мероприятия</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
