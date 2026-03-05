<?php
// Применяем часовой пояс пользователя, если он авторизован
if (\Core\Session::isAuthenticated()) {
    static $__layoutUser = null;
    if ($__layoutUser === null) {
        $__layoutUser = (new \App\Models\User())->getUserById(\Core\Session::userId());
    }
    if (!empty($__layoutUser['timezone'])) {
        date_default_timezone_set($__layoutUser['timezone']);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Underground Psy' ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <!-- Шапка -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">Underground Psy</a>

                <nav class="nav">
                    <a href="/psychologists" class="nav-link">Психологи</a>
                    <a href="/events" class="nav-link">Мероприятия</a>
                    <a href="/questions" class="nav-link">Спросить психолога</a>
                    <a href="/about" class="nav-link">О проекте</a>
                    <?php if (\Core\Session::isAuthenticated()): ?>
                        <?php if (isset($__layoutUser) && $__layoutUser['role'] === 'ADMIN'): ?>
                            <a href="/admin" class="nav-link nav-link--admin">Админ</a>
                        <?php endif; ?>
                        <a href="/dashboard" class="nav-link">Кабинет</a>
                        <a href="/settings" class="nav-link">Настройки</a>
                        <form action="/logout" method="POST" style="display: inline;">
                            <button type="submit" class="btn btn-outline">Выйти</button>
                        </form>
                    <?php else: ?>
                        <a href="/login" class="nav-link">Войти</a>
                        <a href="/register" class="btn btn-primary">Регистрация</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Flash сообщения -->
    <?php if ($success = \Core\Session::getFlash('success')): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($errors = \Core\Session::getFlash('errors')): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Основной контент -->
    <main class="main">
        <?php echo $content ?? '' ?>
    </main>

    <!-- Подвал -->
    <footer class="footer">
        <div class="container">
            <div class="footer-links">
                <a href="/psychologists">Психологи</a>
                <a href="/events">Мероприятия</a>
                <a href="/questions">Спросить психолога</a>
                <a href="/business">Бизнесу</a>
                <a href="/medical">Медучреждениям</a>
                <a href="/about">О проекте</a>
            </div>
            <p>&copy; 2026 Underground Psy. Некоммерческая платформа.</p>
        </div>
    </footer>
</body>
</html>
