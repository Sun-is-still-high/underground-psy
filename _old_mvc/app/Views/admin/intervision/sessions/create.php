<?php
$title = 'Планирование сессии - ' . htmlspecialchars($group['name']);
$old = \Core\Session::getFlash('old') ?? [];
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Планирование сессии интервизии</h1>
        <a href="/admin/intervision/groups/<?= $group['id'] ?>" class="btn btn-outline">Назад к группе</a>
    </div>

    <p class="subtitle">Группа: <strong><?= htmlspecialchars($group['name']) ?></strong></p>

    <div class="form-container">
        <form action="/admin/intervision/groups/<?= $group['id'] ?>/sessions" method="POST">
            <div class="form-group">
                <label for="topic">Тема сессии *</label>
                <input type="text" id="topic" name="topic" required
                       value="<?= htmlspecialchars($old['topic'] ?? '') ?>"
                       placeholder="Например: Работа с тревожностью">
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="3"
                          placeholder="Дополнительная информация о сессии"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="scheduled_at">Дата и время *</label>
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at" required
                           value="<?= htmlspecialchars($old['scheduled_at'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="duration_minutes">Длительность (мин)</label>
                    <input type="number" id="duration_minutes" name="duration_minutes"
                           min="30" max="240" value="<?= htmlspecialchars($old['duration_minutes'] ?? '90') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="meeting_link">Ссылка на встречу (Zoom/Google Meet)</label>
                <input type="url" id="meeting_link" name="meeting_link"
                       value="<?= htmlspecialchars($old['meeting_link'] ?? '') ?>"
                       placeholder="https://zoom.us/j/...">
                <small>Можно добавить позже</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Запланировать сессию</button>
                <a href="/admin/intervision/groups/<?= $group['id'] ?>" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../../layouts/main.php';
?>
