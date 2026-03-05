<?php
$title = 'Мои запросы - Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Мои запросы на помощь</h1>
        <a href="/client/cases/create" class="btn btn-primary">Создать запрос</a>
    </div>

    <?php if (empty($cases)): ?>
        <div class="empty-state">
            <p>У вас пока нет запросов на помощь</p>
            <p>Создайте запрос, чтобы психологи могли откликнуться и предложить свои услуги</p>
            <a href="/client/cases/create" class="btn btn-primary">Создать первый запрос</a>
        </div>
    <?php else: ?>
        <div class="cases-list">
            <?php foreach ($cases as $case): ?>
                <div class="case-card">
                    <div class="case-header">
                        <h3><?= htmlspecialchars($case['title']) ?></h3>
                        <span class="badge badge-<?= strtolower($case['status']) ?>">
                            <?php
                            $statusLabels = [
                                'OPEN' => 'Открыт',
                                'IN_PROGRESS' => 'В работе',
                                'CLOSED' => 'Закрыт',
                                'CANCELLED' => 'Отменён'
                            ];
                            echo $statusLabels[$case['status']] ?? $case['status'];
                            ?>
                        </span>
                    </div>
                    <div class="case-meta">
                        <span class="problem-type"><?= htmlspecialchars($case['problem_type_name']) ?></span>
                        <span class="date"><?= date('d.m.Y', strtotime($case['created_at'])) ?></span>
                        <?php if ($case['responses_count'] > 0): ?>
                            <span class="responses-count"><?= $case['responses_count'] ?> откликов</span>
                        <?php endif; ?>
                    </div>
                    <p class="case-description"><?= htmlspecialchars(mb_substr($case['description'], 0, 150)) ?>...</p>
                    <div class="case-actions">
                        <a href="/client/cases/<?= $case['id'] ?>" class="btn btn-outline btn-sm">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>
