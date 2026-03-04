<?php
$title = 'Главная - Underground Psy';
ob_start();
?>

<div class="container">
    <div class="hero">
        <h1>Добро пожаловать в Underground Psy</h1>
        <p class="hero-subtitle">Некоммерческая платформа для связи клиентов с начинающими психологами</p>

        <?php if (isset($user)): ?>
            <div class="welcome-box">
                <h2>Здравствуйте, <?= htmlspecialchars($user['name']) ?>!</h2>
                <p>Роль: <strong><?= htmlspecialchars($user['role']) ?></strong></p>
                <a href="/dashboard" class="btn btn-primary">Перейти в личный кабинет</a>
            </div>
        <?php else: ?>
            <div class="cta-buttons">
                <a href="/register" class="btn btn-primary btn-lg">Начать работу</a>
                <a href="/login" class="btn btn-outline btn-lg">Войти</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="features">
        <h2>Как это работает</h2>
        <div class="features-grid">
            <div class="feature-card">
                <h3>1. Регистрация</h3>
                <p>Создайте аккаунт как клиент или психолог</p>
            </div>
            <div class="feature-card">
                <h3>2. Поиск специалиста</h3>
                <p>Выберите психолога по методу терапии</p>
            </div>
            <div class="feature-card">
                <h3>3. Бронирование</h3>
                <p>Запишитесь на консультацию в удобное время</p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
?>
