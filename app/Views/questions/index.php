<?php
$title = 'Ответы психологов — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Спросить психолога</h1>
        <p class="page-subtitle">Ответы специалистов на реальные вопросы пользователей</p>
        <a href="/ask" class="btn btn-primary">Задать вопрос</a>
    </div>

    <?php if (empty($questions)): ?>
        <div class="empty-state">
            <p>Пока вопросов нет. Будьте первым!</p>
            <a href="/ask" class="btn btn-primary" style="margin-top:1rem;">Задать вопрос</a>
        </div>
    <?php else: ?>
        <div class="qa-list">
            <?php foreach ($questions as $q): ?>
                <div class="qa-item">
                    <div class="qa-question">
                        <span class="qa-author"><?= htmlspecialchars($q['author_name']) ?> спрашивает:</span>
                        <p><?= nl2br(htmlspecialchars($q['question'])) ?></p>
                        <span class="qa-date"><?= date('d.m.Y', strtotime($q['created_at'])) ?></span>
                    </div>
                    <div class="qa-answer">
                        <div class="qa-answer-header">
                            <?php if (!empty($q['psychologist_photo'])): ?>
                                <img src="<?= htmlspecialchars($q['psychologist_photo']) ?>" alt="" class="qa-psy-photo">
                            <?php else: ?>
                                <div class="psy-avatar psy-avatar-sm"><?= mb_substr($q['psychologist_name'], 0, 1) ?></div>
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($q['psychologist_name']) ?></strong>
                            <span class="qa-answer-date"><?= date('d.m.Y', strtotime($q['answered_at'])) ?></span>
                        </div>
                        <p><?= nl2br(htmlspecialchars($q['answer'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
