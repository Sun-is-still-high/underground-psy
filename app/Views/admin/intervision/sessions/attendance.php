<?php
$title = 'Отметка посещаемости - ' . htmlspecialchars($session['topic']);
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Отметка посещаемости</h1>
        <a href="/admin/intervision/sessions/<?= $session['id'] ?>" class="btn btn-outline">Назад к сессии</a>
    </div>

    <div class="session-info">
        <p><strong>Сессия:</strong> <?= htmlspecialchars($session['topic']) ?></p>
        <p><strong>Группа:</strong> <?= htmlspecialchars($group['name']) ?></p>
        <p><strong>Дата:</strong> <?= date('d.m.Y H:i', strtotime($session['scheduled_at'])) ?></p>
    </div>

    <?php if (empty($session['attendance'])): ?>
        <div class="alert alert-warning">
            <p>В группе нет участников для отметки посещаемости.</p>
        </div>
    <?php else: ?>
        <form action="/admin/intervision/sessions/<?= $session['id'] ?>/attendance" method="POST">
            <div class="attendance-list">
                <p class="help-text">Отметьте участников, которые присутствовали на сессии:</p>

                <?php foreach ($session['attendance'] as $record): ?>
                <div class="attendance-item">
                    <label class="checkbox-label">
                        <input type="checkbox"
                               name="attended[]"
                               value="<?= $record['participant_id'] ?>"
                               <?= $record['attended'] ? 'checked' : '' ?>>
                        <span class="participant-name"><?= htmlspecialchars($record['name']) ?></span>
                        <span class="participant-email">(<?= htmlspecialchars($record['email']) ?>)</span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить посещаемость</button>
                <a href="/admin/intervision/sessions/<?= $session['id'] ?>" class="btn btn-outline">Отмена</a>
            </div>

            <p class="note">После сохранения сессия будет автоматически отмечена как завершённая.</p>
        </form>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../../layouts/main.php';
?>
