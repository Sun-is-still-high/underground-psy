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
                    <a href="/about" class="nav-link">О проекте</a>
                    <?php if (\Core\Session::isAuthenticated()): ?>
                        <a href="/dashboard" class="nav-link">Личный кабинет</a>
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
            <p>&copy; 2026 Underground Psy. Некоммерческая платформа.</p>
        </div>
    </footer>
</body>
</html>
