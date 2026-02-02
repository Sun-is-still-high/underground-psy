<?php
$title = 'Вход - Underground Psy';
ob_start();
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Вход в систему</h1>

            <form action="/login" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars(\Core\Session::getFlash('old_email') ?? '') ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block">Войти</button>
            </form>

            <div class="auth-footer">
                <p>Нет аккаунта? <a href="/register">Зарегистрироваться</a></p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
