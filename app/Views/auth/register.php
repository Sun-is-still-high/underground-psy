<?php
$title = 'Регистрация - Underground Psy';
ob_start();
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Регистрация</h1>

            <form action="/register" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name">Имя</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="<?= htmlspecialchars(\Core\Session::getFlash('old_name') ?? '') ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars(\Core\Session::getFlash('old_email') ?? '') ?>"
                        required
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
                        minlength="6"
                    >
                    <small>Минимум 6 символов</small>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Подтверждение пароля</label>
                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        class="form-control"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="role">Я хочу зарегистрироваться как:</label>
                    <select id="role" name="role" class="form-control">
                        <option value="CLIENT" <?= (\Core\Session::getFlash('old_role') ?? 'CLIENT') === 'CLIENT' ? 'selected' : '' ?>>
                            Клиент
                        </option>
                        <option value="PSYCHOLOGIST" <?= (\Core\Session::getFlash('old_role') ?? '') === 'PSYCHOLOGIST' ? 'selected' : '' ?>>
                            Психолог
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
            </form>

            <div class="auth-footer">
                <p>Уже есть аккаунт? <a href="/login">Войти</a></p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
