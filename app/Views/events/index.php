<?php
$title = 'Мероприятия — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Мероприятия</h1>
        <p class="page-subtitle">Групповая терапия, группы поддержки, семинары и тренинги</p>
    </div>

    <!-- Фильтры -->
    <div class="filters-bar">
        <form action="/events" method="GET" class="filter-form">
            <div class="filter-group">
                <select name="event_type" class="form-control">
                    <option value="">Все типы</option>
                    <?php foreach ($types as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($filters['event_type'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <select name="format" class="form-control">
                    <option value="">Любой формат</option>
                    <?php foreach ($formats as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($filters['format'] ?? '') === $val ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Найти</button>
            <?php if (!empty($filters['event_type']) || !empty($filters['format'])): ?>
                <a href="/events" class="btn btn-outline">Сбросить</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($events)): ?>
        <div class="empty-state">
            <p>Предстоящих мероприятий пока нет.</p>
            <?php if (\Core\Session::isAuthenticated()): ?>
                <a href="/psychologist/events/create" class="btn btn-primary" style="margin-top:1rem;">Создать мероприятие</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <div class="event-type-badge">
                        <?= htmlspecialchars($types[$event['event_type']] ?? $event['event_type']) ?>
                    </div>

                    <h3 class="event-title">
                        <a href="/events/<?= $event['id'] ?>"><?= htmlspecialchars($event['title']) ?></a>
                    </h3>

                    <div class="event-meta">
                        <span class="event-date">📅 <?= date('d.m.Y H:i', strtotime($event['scheduled_at'])) ?></span>
                        <span class="event-format badge <?= $event['format'] === 'ONLINE' ? 'badge-online' : 'badge-offline' ?>">
                            <?= $formats[$event['format']] ?? $event['format'] ?>
                        </span>
                        <?php if (!empty($event['city'])): ?>
                            <span class="event-city">📍 <?= htmlspecialchars($event['city']) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($event['description'])): ?>
                        <p class="event-desc"><?= htmlspecialchars(mb_substr($event['description'], 0, 120)) ?><?= mb_strlen($event['description']) > 120 ? '…' : '' ?></p>
                    <?php endif; ?>

                    <div class="event-footer">
                        <span class="event-price">
                            <?= $event['price'] ? number_format($event['price'], 0, '', ' ') . ' ₽' : 'Бесплатно' ?>
                        </span>
                        <?php if ($event['max_participants']): ?>
                            <span class="event-seats">Мест: <?= $event['max_participants'] ?></span>
                        <?php endif; ?>
                        <a href="/events/<?= $event['id'] ?>" class="btn btn-outline btn-sm">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (\Core\Session::isAuthenticated()): ?>
        <div style="margin-top: 2rem; text-align:center;">
            <a href="/psychologist/events/create" class="btn btn-primary">+ Создать мероприятие</a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
