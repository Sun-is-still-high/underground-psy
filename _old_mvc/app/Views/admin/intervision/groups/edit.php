<?php
$title = 'Редактирование группы - ' . htmlspecialchars($group['name']);
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Редактирование группы</h1>
        <a href="/admin/intervision/groups/<?= $group['id'] ?>" class="btn btn-outline">Назад к группе</a>
    </div>

    <div class="form-container">
        <form action="/admin/intervision/groups/<?= $group['id'] ?>" method="POST">
            <div class="form-group">
                <label for="name">Название группы *</label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($group['name']) ?>">
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($group['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="max_participants">Максимум участников *</label>
                <input type="number" id="max_participants" name="max_participants"
                       min="2" max="50" value="<?= $group['max_participants'] ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="/admin/intervision/groups/<?= $group['id'] ?>" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../../layouts/main.php';
?>
