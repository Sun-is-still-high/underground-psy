<?php
$title = 'Психологи — Underground Psy';

$formatLabels = ['ONLINE' => 'Онлайн', 'OFFLINE' => 'Офлайн', 'BOTH' => 'Онлайн и офлайн'];

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
            <div class="filter-row">
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
                    <select name="gender" class="form-control">
                        <option value="">Любой пол</option>
                        <option value="FEMALE" <?= ($filters['gender'] ?? '') === 'FEMALE' ? 'selected' : '' ?>>Женщина</option>
                        <option value="MALE"   <?= ($filters['gender'] ?? '') === 'MALE'   ? 'selected' : '' ?>>Мужчина</option>
                    </select>
                </div>

                <div class="filter-group">
                    <select name="work_format" class="form-control">
                        <option value="">Любой формат</option>
                        <option value="ONLINE"  <?= ($filters['work_format'] ?? '') === 'ONLINE'  ? 'selected' : '' ?>>Онлайн</option>
                        <option value="OFFLINE" <?= ($filters['work_format'] ?? '') === 'OFFLINE' ? 'selected' : '' ?>>Офлайн</option>
                    </select>
                </div>

                <div class="filter-group">
                    <input type="text" name="language" class="form-control"
                           placeholder="Язык (напр. Английский)"
                           value="<?= htmlspecialchars($filters['language'] ?? '') ?>">
                </div>

                <div class="filter-group">
                    <input type="number" name="price_max" class="form-control"
                           placeholder="Цена до (₽/час)" min="0" step="100"
                           value="<?= htmlspecialchars($filters['price_max'] ?? '') ?>">
                </div>

                <div class="filter-group">
                    <input type="text" name="search" class="form-control"
                           placeholder="Поиск по имени..."
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Найти</button>
                <?php
                $hasFilters = !empty($filters['problem_type_id']) || !empty($filters['search'])
                           || !empty($filters['gender']) || !empty($filters['work_format'])
                           || !empty($filters['language']) || !empty($filters['price_max']);
                if ($hasFilters):
                ?>
                    <a href="/psychologists" class="btn btn-outline">Сбросить</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Список -->
    <?php if (empty($psychologists)): ?>
        <div class="empty-state">
            <p>Психологов по выбранным критериям не найдено.</p>
            <p>Попробуйте изменить фильтры или загляните позже.</p>
        </div>
    <?php else: ?>
        <p class="results-count">Найдено: <?= count($psychologists) ?></p>
        <div class="psychologists-grid">
            <?php foreach ($psychologists as $psy): ?>
                <div class="psychologist-card">
                    <div class="psy-card-header">
                        <!-- Фото или инициал -->
                        <?php if (!empty($psy['photo_url'])): ?>
                            <img src="<?= htmlspecialchars($psy['photo_url']) ?>"
                                 alt="<?= htmlspecialchars($psy['psychologist_name']) ?>"
                                 class="psy-avatar-photo">
                        <?php else: ?>
                            <div class="psy-avatar"><?= mb_substr($psy['psychologist_name'], 0, 1) ?></div>
                        <?php endif; ?>

                        <div>
                            <h3 class="psy-name">
                                <?= htmlspecialchars($psy['psychologist_name']) ?>
                                <?php if ($psy['diploma_verified']): ?>
                                    <span class="badge-verified" title="Диплом верифицирован">✓</span>
                                <?php endif; ?>
                            </h3>
                            <span class="psy-since">с <?= date('Y', strtotime($psy['registered_at'])) ?> г.</span>
                        </div>
                    </div>

                    <!-- Формат работы -->
                    <div class="psy-meta">
                        <?php
                        $fmt = $psy['work_format'] ?? 'ONLINE';
                        $fmtClass = match($fmt) {
                            'ONLINE'  => 'badge-online',
                            'OFFLINE' => 'badge-offline',
                            default   => 'badge-both',
                        };
                        ?>
                        <span class="badge <?= $fmtClass ?>"><?= $formatLabels[$fmt] ?? $fmt ?></span>
                        <?php if (!empty($psy['city']) && $fmt !== 'ONLINE'): ?>
                            <span class="psy-city">📍 <?= htmlspecialchars($psy['city']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Специализации -->
                    <?php if (!empty($psy['specializations'])): ?>
                        <div class="psy-specializations">
                            <?php foreach (array_slice($psy['specializations'], 0, 3) as $spec): ?>
                                <span class="badge badge-primary"><?= htmlspecialchars($spec['name']) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($psy['specializations']) > 3): ?>
                                <span class="badge badge-outline">+<?= count($psy['specializations']) - 3 ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Bio (только FULL режим) -->
                    <?php if (!empty($psy['bio']) && ($psy['profile_mode'] ?? 'FULL') === 'FULL'): ?>
                        <p class="psy-bio"><?= htmlspecialchars(mb_substr($psy['bio'], 0, 120)) ?><?= mb_strlen($psy['bio']) > 120 ? '…' : '' ?></p>
                    <?php endif; ?>

                    <!-- Стоимость (только FULL режим) -->
                    <?php if (($psy['profile_mode'] ?? 'FULL') === 'FULL' && ($psy['hourly_rate_min'] || $psy['hourly_rate_max'])): ?>
                        <div class="psy-rate">
                            <?php if ($psy['hourly_rate_min'] && $psy['hourly_rate_max']): ?>
                                <?= number_format($psy['hourly_rate_min'], 0, '', ' ') ?> – <?= number_format($psy['hourly_rate_max'], 0, '', ' ') ?> ₽/час
                            <?php elseif ($psy['hourly_rate_min']): ?>
                                от <?= number_format($psy['hourly_rate_min'], 0, '', ' ') ?> ₽/час
                            <?php else: ?>
                                до <?= number_format($psy['hourly_rate_max'], 0, '', ' ') ?> ₽/час
                            <?php endif; ?>
                        </div>
                    <?php elseif (($psy['profile_mode'] ?? 'FULL') === 'REGISTRY'): ?>
                        <div class="psy-rate psy-rate-registry">В реестре</div>
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
