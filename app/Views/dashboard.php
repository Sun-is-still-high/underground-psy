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
                        <h3>Найти психолога</h3>
                        <p>Поиск специалистов по методам терапии</p>
                        <a href="/psychologists" class="btn btn-primary">Перейти к поиску</a>
                    </div>
                    <div class="action-card">
                        <h3>Мои сеансы</h3>
                        <p>Просмотр забронированных консультаций</p>
                        <a href="/sessions" class="btn btn-outline">Мои записи</a>
                    </div>
                </div>
            </div>
        <?php elseif ($user['role'] === 'PSYCHOLOGIST'): ?>
            <div class="dashboard-section">
                <h2>Мои действия</h2>
                <div class="action-cards">
                    <div class="action-card">
                        <h3>Мое расписание</h3>
                        <p>Управление слотами доступности</p>
                        <a href="/schedule" class="btn btn-primary">Управление</a>
                    </div>
                    <div class="action-card">
                        <h3>Мои сеансы</h3>
                        <p>Просмотр забронированных сеансов</p>
                        <a href="/sessions" class="btn btn-outline">Мои клиенты</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="dashboard-note">
            <p><strong>Примечание:</strong> Функционал расписания и бронирования будет доступен в следующих версиях.</p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
?>
