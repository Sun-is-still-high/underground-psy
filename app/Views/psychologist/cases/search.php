<?php
$title = 'Поиск кейсов - Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Поиск кейсов</h1>
    </div>

    <p class="subtitle">Найдите клиентов, которым вы можете помочь</p>

    <!-- Статистика по типам проблем -->
    <div class="stats-grid">
        <?php foreach ($stats as $stat): ?>
            <a href="?problem_type=<?= $stat['id'] ?>" class="stat-card <?= $filters['problem_type'] == $stat['id'] ? 'stat-active' : '' ?>">
                <div class="stat-value"><?= $stat['cases_count'] ?></div>
                <div class="stat-label"><?= htmlspecialchars($stat['name']) ?></div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Фильтры -->
    <div class="filters-bar">
        <form action="/psychologist/cases" method="GET" class="filter-form">
            <div class="filter-group">
                <label>Тип проблемы:</label>
                <select name="problem_type" class="form-control">
                    <option value="">Все типы</option>
                    <?php foreach ($problemTypes as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= $filters['problem_type'] == $type['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Формат оплаты:</label>
                <select name="budget_type" class="form-control">
                    <option value="">Любой</option>
                    <option value="PAID" <?= $filters['budget_type'] === 'PAID' ? 'selected' : '' ?>>Платно</option>
                    <option value="REVIEW" <?= $filters['budget_type'] === 'REVIEW' ? 'selected' : '' ?>>Оплата отзывом</option>
                    <option value="NEGOTIABLE" <?= $filters['budget_type'] === 'NEGOTIABLE' ? 'selected' : '' ?>>Договорная</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Применить</button>
            <?php if ($filters['problem_type'] || $filters['budget_type']): ?>
                <a href="/psychologist/cases" class="btn btn-outline btn-sm">Сбросить</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Список кейсов -->
    <?php if (empty($cases)): ?>
        <div class="empty-state">
            <p>Кейсов по выбранным фильтрам не найдено</p>
            <p>Попробуйте изменить параметры поиска или загляните позже</p>
        </div>
    <?php else: ?>
        <div class="cases-list">
            <?php foreach ($cases as $case): ?>
                <div class="case-card">
                    <div class="case-header">
                        <h3><?= htmlspecialchars($case['title']) ?></h3>
                        <span class="badge badge-<?= strtolower(str_replace('_', '-', $case['budget_type'])) ?>">
                            <?php
                            $budgetLabels = [
                                'PAID' => 'Платно',
                                'REVIEW' => 'За отзыв',
                                'NEGOTIABLE' => 'Договорная'
                            ];
                            echo $budgetLabels[$case['budget_type']] ?? $case['budget_type'];
                            ?>
                        </span>
                    </div>
                    <div class="case-meta">
                        <span class="problem-type"><?= htmlspecialchars($case['problem_type_name']) ?></span>
                        <span class="client-name">от <?= htmlspecialchars($case['client_name']) ?></span>
                        <span class="date"><?= date('d.m.Y', strtotime($case['created_at'])) ?></span>
                    </div>
                    <p class="case-description"><?= htmlspecialchars(mb_substr($case['description'], 0, 200)) ?>...</p>
                    <?php if ($case['budget_type'] === 'PAID' && $case['budget_amount']): ?>
                        <div class="case-budget">
                            Бюджет: <strong><?= number_format($case['budget_amount'], 0, '', ' ') ?> руб.</strong>
                        </div>
                    <?php endif; ?>
                    <div class="case-actions">
                        <a href="/psychologist/cases/<?= $case['id'] ?>" class="btn btn-primary btn-sm">Подробнее</a>
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
