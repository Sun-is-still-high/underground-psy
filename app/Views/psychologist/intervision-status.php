<?php
$title = 'Мои интервизии - Underground Psy';
$monthNames = [
    1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
    5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
    9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
];
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Мои интервизии</h1>
        <a href="/dashboard" class="btn btn-outline">Назад в кабинет</a>
    </div>

    <!-- Статус права консультировать -->
    <div class="status-card <?= $status['can_consult'] ? 'status-ok' : 'status-warning' ?>">
        <?php if ($status['can_consult']): ?>
            <div class="status-icon status-icon-ok">&#10003;</div>
            <h2>Вы можете консультировать</h2>
            <p>Требования по интервизиям за <?= $monthNames[$status['month']] ?> выполнены.</p>
        <?php else: ?>
            <div class="status-icon status-icon-warning">!</div>
            <h2>Требуется посещение интервизий</h2>
            <p>Для права консультировать посетите ещё <strong><?= $status['remaining'] ?></strong>
               <?= $status['remaining'] == 1 ? 'сессию' : ($status['remaining'] < 5 ? 'сессии' : 'сессий') ?>
               в <?= $monthNames[$status['month']] ?>.</p>
        <?php endif; ?>

        <div class="status-details">
            <div class="progress-info">
                <span>Посещено в этом месяце:</span>
                <strong><?= $status['attended_this_month'] ?> / <?= $status['required_per_month'] ?></strong>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= min(100, ($status['attended_this_month'] / $status['required_per_month']) * 100) ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Мои группы -->
    <div class="section">
        <h2>Мои группы интервизий</h2>

        <?php if (empty($status['groups'])): ?>
            <div class="empty-state">
                <p>Вы пока не состоите ни в одной группе интервизий.</p>
                <p>Обратитесь к администратору для добавления в группу.</p>
            </div>
        <?php else: ?>
            <div class="groups-list">
                <?php foreach ($status['groups'] as $group): ?>
                <div class="group-card">
                    <h3><?= htmlspecialchars($group['name']) ?></h3>
                    <?php if ($group['description']): ?>
                        <p><?= htmlspecialchars($group['description']) ?></p>
                    <?php endif; ?>
                    <div class="group-meta">
                        <span>В группе с <?= date('d.m.Y', strtotime($group['joined_at'])) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.status-card {
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: center;
}
.status-ok {
    background: #d4edda;
    border: 1px solid #c3e6cb;
}
.status-warning {
    background: #fff3cd;
    border: 1px solid #ffeeba;
}
.status-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}
.status-icon-ok { color: #28a745; }
.status-icon-warning { color: #856404; }
.status-details {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(0,0,0,0.1);
}
.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}
.progress-bar {
    height: 10px;
    background: rgba(0,0,0,0.1);
    border-radius: 5px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: #28a745;
    transition: width 0.3s;
}
.groups-list {
    display: grid;
    gap: 1rem;
}
.group-card {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}
.group-card h3 {
    margin: 0 0 0.5rem 0;
}
.group-meta {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}
.empty-state {
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
