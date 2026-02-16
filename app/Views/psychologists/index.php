<?php
$title = 'Психологи - Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Наши психологи</h1>
        <p class="page-subtitle">Начинающие специалисты, готовые помочь</p>
    </div>

    <!-- Фильтры -->
    <div class="filters-bar">
        <form action="/psychologists" method="GET" class="filter-form">
            <div class="filter-group">
                <select name="specialization" class="form-control">
                    <option value="">Все специализации</option>
                    <?php foreach ($problemTypes as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= ($filters['problem_type_id'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <input type="text" name="search" class="form-control"
                       placeholder="Поиск по имени..."
                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Найти</button>
            <?php if (!empty($filters['problem_type_id']) || !empty($filters['search'])): ?>
                <a href="/psychologists" class="btn btn-outline">Сбросить</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Список -->
    <?php if (empty($psychologists)): ?>
        <div class="empty-state">
            <p>Психологов по выбранным критериям не найдено.</p>
            <p>Попробуйте изменить фильтры или загляните позже.</p>
        </div>
    <?php else: ?>
        <div class="psychologists-grid">
            <?php foreach ($psychologists as $psy): ?>
                <div class="psychologist-card">
                    <div class="psy-card-header">
                        <div class="psy-avatar"><?= mb_substr($psy['psychologist_name'], 0, 1) ?></div>
                        <div>
                            <h3 class="psy-name"><?= htmlspecialchars($psy['psychologist_name']) ?></h3>
                            <span class="psy-since">На платформе с <?= date('d.m.Y', strtotime($psy['registered_at'])) ?></span>
                        </div>
                    </div>

                    <?php if (!empty($psy['specializations'])): ?>
                        <div class="psy-specializations">
                            <?php foreach ($psy['specializations'] as $spec): ?>
                                <span class="badge badge-primary"><?= htmlspecialchars($spec['name']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($psy['bio'])): ?>
                        <p class="psy-bio"><?= htmlspecialchars(mb_substr($psy['bio'], 0, 150)) ?><?= mb_strlen($psy['bio']) > 150 ? '...' : '' ?></p>
                    <?php endif; ?>

                    <?php if ($psy['hourly_rate_min'] || $psy['hourly_rate_max']): ?>
                        <div class="psy-rate">
                            <?php if ($psy['hourly_rate_min'] && $psy['hourly_rate_max']): ?>
                                <?= number_format($psy['hourly_rate_min'], 0, '', ' ') ?> – <?= number_format($psy['hourly_rate_max'], 0, '', ' ') ?> ₽/час
                            <?php elseif ($psy['hourly_rate_min']): ?>
                                от <?= number_format($psy['hourly_rate_min'], 0, '', ' ') ?> ₽/час
                            <?php else: ?>
                                до <?= number_format($psy['hourly_rate_max'], 0, '', ' ') ?> ₽/час
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="psy-card-actions">
                        <a href="/psychologists/<?= $psy['id'] ?>" class="btn btn-primary">Подробнее</a>
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
