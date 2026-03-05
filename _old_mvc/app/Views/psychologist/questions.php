<?php
$title = 'Вопросы пользователей — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Вопросы пользователей</h1>
        <p class="page-subtitle">Ответьте на вопросы — ваш ответ будет опубликован публично</p>
    </div>

    <?php if (empty($questions)): ?>
        <div class="empty-state">
            <p>Новых вопросов пока нет. Загляните позже.</p>
        </div>
    <?php else: ?>
        <div class="qa-admin-list">
            <?php foreach ($questions as $q): ?>
                <div class="qa-admin-item">
                    <div class="qa-question-block">
                        <div class="qa-question-meta">
                            <span class="qa-author"><?= htmlspecialchars($q['author_name']) ?></span>
                            <span class="qa-date"><?= date('d.m.Y H:i', strtotime($q['created_at'])) ?></span>
                        </div>
                        <p class="qa-question-text"><?= nl2br(htmlspecialchars($q['question'])) ?></p>
                    </div>

                    <form action="/psychologist/questions/<?= $q['id'] ?>/answer" method="POST" class="qa-answer-form">
                        <div class="form-group">
                            <label>Ваш ответ</label>
                            <textarea name="answer" class="form-control" rows="4"
                                      placeholder="Напишите развёрнутый ответ..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Опубликовать ответ</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
