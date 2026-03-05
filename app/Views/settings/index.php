<?php
$title = 'Настройки — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Настройки аккаунта</h1>
    </div>

    <div class="settings-page">

        <!-- Часовой пояс -->
        <div class="settings-section">
            <h2>Часовой пояс</h2>
            <p class="text-muted">Расписание специалистов и даты будут отображаться в выбранном часовом поясе.</p>

            <form action="/settings/timezone" method="POST">
                <div class="form-group">
                    <label for="timezone">Ваш часовой пояс</label>
                    <select name="timezone" id="timezone" class="form-control" style="max-width:400px;">
                        <?php foreach ($timezones as $tz => $label): ?>
                            <option value="<?= $tz ?>" <?= ($user['timezone'] ?? 'Europe/Moscow') === $tz ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>

        <!-- Информация об аккаунте -->
        <div class="settings-section">
            <h2>Аккаунт</h2>
            <div class="info-row">
                <span class="label">Email:</span>
                <span class="value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Имя:</span>
                <span class="value"><?= htmlspecialchars($user['name']) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Роль:</span>
                <span class="value">
                    <?= ['CLIENT' => 'Клиент', 'PSYCHOLOGIST' => 'Психолог', 'ADMIN' => 'Администратор'][$user['role']] ?? $user['role'] ?>
                </span>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
