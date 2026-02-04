<?php
$title = 'Создание группы интервизий';
$old = \Core\Session::getFlash('old') ?? [];
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Создание группы интервизий</h1>
        <a href="/admin/intervision/groups" class="btn btn-outline">Назад к списку</a>
    </div>

    <div class="form-container">
        <form action="/admin/intervision/groups" method="POST">
            <div class="form-group">
                <label for="name">Название группы *</label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                       placeholder="Например: Группа интервизий #1">
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="4"
                          placeholder="Опишите цели и специфику группы"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="max_participants">Максимум участников *</label>
                <input type="number" id="max_participants" name="max_participants"
                       min="2" max="50" value="<?= htmlspecialchars($old['max_participants'] ?? '10') ?>">
                <small>От 2 до 50 человек</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Создать группу</button>
                <a href="/admin/intervision/groups" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../../layouts/main.php';
?>
