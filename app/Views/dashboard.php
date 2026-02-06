<?php
$title = 'Личный кабинет - Underground Psy';
ob_start();
?>

<div class="container">
    <div class="dashboard">
        <h1>Личный кабинет</h1>

        <div class="user-info-card">
            <h2>Информация о профиле</h2>
            <div class="info-row">
                <span class="label">Имя:</span>
                <span class="value"><?= htmlspecialchars($user['name']) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Роль:</span>
                <span class="value">
                    <?php
                    $roleNames = [
                        'CLIENT' => 'Клиент',
                        'PSYCHOLOGIST' => 'Психолог',
                        'ADMIN' => 'Администратор'
                    ];
                    echo htmlspecialchars($roleNames[$user['role']] ?? $user['role']);
                    ?>
                </span>
            </div>
            <div class="info-row">
                <span class="label">Дата регистрации:</span>
                <span class="value"><?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
            </div>
        </div>

        <?php if ($user['role'] === 'CLIENT'): ?>
            <div class="dashboard-section">
                <h2>Мои действия</h2>
                <div class="action-cards">
                    <div class="action-card">
                        <h3>Создать запрос</h3>
                        <p>Опишите проблему - психологи откликнутся</p>
                        <a href="/client/cases/create" class="btn btn-primary">Создать запрос</a>
                    </div>
                    <div class="action-card">
                        <h3>Мои запросы</h3>
                        <p>Просмотр запросов и откликов</p>
                        <a href="/client/cases" class="btn btn-outline">Перейти</a>
                    </div>
                </div>
            </div>
        <?php elseif ($user['role'] === 'PSYCHOLOGIST'): ?>
            <div class="dashboard-section">
                <h2>Мои действия</h2>
                <div class="action-cards">
                    <div class="action-card">
                        <h3>Поиск кейсов</h3>
                        <p>Найти клиентов по типу проблемы</p>
                        <a href="/psychologist/cases" class="btn btn-primary">Найти кейсы</a>
                    </div>
                    <div class="action-card">
                        <h3>Мои интервизии</h3>
                        <p>Статус посещения и группы</p>
                        <a href="/psychologist/intervisions" class="btn btn-outline">Перейти</a>
                    </div>
                </div>
            </div>
        <?php elseif ($user['role'] === 'ADMIN'): ?>
            <div class="dashboard-section">
                <h2>Администрирование</h2>
                <div class="action-cards">
                    <div class="action-card">
                        <h3>Интервизии</h3>
                        <p>Управление группами и сессиями</p>
                        <a href="/admin/intervision/groups" class="btn btn-primary">Управление</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
?>
