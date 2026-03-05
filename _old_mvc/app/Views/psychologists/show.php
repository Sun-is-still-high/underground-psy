<?php
$title = htmlspecialchars($profile['psychologist_name']) . ' — Психолог — Underground Psy';

$formatLabels = ['ONLINE' => 'Онлайн', 'OFFLINE' => 'Офлайн', 'BOTH' => 'Онлайн и офлайн'];
$audienceLabels = [
    'ADULTS'   => 'Взрослые',
    'COUPLES'  => 'Пары и семьи',
    'TEENS'    => 'Подростки',
    'CHILDREN' => 'Дети и родители',
    'BUSINESS' => 'Бизнес-клиенты',
];

$isRegistry    = ($profile['profile_mode'] ?? 'FULL') === 'REGISTRY';
$targetAudience = array_filter(explode(',', $profile['target_audience'] ?? ''));

ob_start();
?>

<div class="container">
    <div class="profile-page">

        <!-- Шапка профиля -->
        <div class="profile-header">
            <?php if (!empty($profile['photo_url'])): ?>
                <img src="<?= htmlspecialchars($profile['photo_url']) ?>"
                     alt="<?= htmlspecialchars($profile['psychologist_name']) ?>"
                     class="profile-photo-lg">
            <?php else: ?>
                <div class="profile-avatar-lg"><?= mb_substr($profile['psychologist_name'], 0, 1) ?></div>
            <?php endif; ?>

            <div class="profile-header-info">
                <h1>
                    <?= htmlspecialchars($profile['psychologist_name']) ?>
                    <?php if ($profile['diploma_verified']): ?>
                        <span class="badge-verified-lg" title="Диплом верифицирован администратором">✓ Верифицирован</span>
                    <?php endif; ?>
                </h1>

                <!-- Формат работы -->
                <div class="profile-meta">
                    <?php
                    $fmt = $profile['work_format'] ?? 'ONLINE';
                    $fmtClass = match($fmt) {
                        'ONLINE'  => 'badge-online',
                        'OFFLINE' => 'badge-offline',
                        default   => 'badge-both',
                    };
                    ?>
                    <span class="badge <?= $fmtClass ?>"><?= $formatLabels[$fmt] ?? $fmt ?></span>

                    <?php if (!empty($profile['city']) && $fmt !== 'ONLINE'): ?>
                        <span class="profile-city">📍 <?= htmlspecialchars($profile['city']) ?></span>
                    <?php endif; ?>

                    <?php if (!empty($profile['languages'])): ?>
                        <span class="profile-languages">🌐 <?= htmlspecialchars($profile['languages']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Специализации -->
                <?php if (!empty($specializations)): ?>
                    <div class="profile-specializations">
                        <?php foreach ($specializations as $spec): ?>
                            <span class="badge badge-primary"><?= htmlspecialchars($spec['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Целевая аудитория -->
                <?php if (!empty($targetAudience)): ?>
                    <div class="profile-audience">
                        <?php foreach ($targetAudience as $aud): ?>
                            <span class="badge badge-outline"><?= htmlspecialchars($audienceLabels[$aud] ?? $aud) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Режим REGISTRY — ограниченная информация -->
        <?php if ($isRegistry): ?>
            <div class="registry-notice">
                Этот специалист присутствует в реестре. Для получения полной информации и контактов
                напишите им через форму запроса или свяжитесь напрямую.
            </div>

        <?php else: ?>
            <!-- FULL режим — полная страница -->

            <div class="profile-content">
                <?php if (!empty($profile['bio'])): ?>
                    <div class="profile-section">
                        <h2>О себе</h2>
                        <p><?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile['methods_description'])): ?>
                    <div class="profile-section">
                        <h2>Методы и подходы</h2>
                        <p><?= nl2br(htmlspecialchars($profile['methods_description'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile['education'])): ?>
                    <div class="profile-section">
                        <h2>Образование</h2>
                        <p><?= nl2br(htmlspecialchars($profile['education'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($profile['experience_description'])): ?>
                    <div class="profile-section">
                        <h2>Опыт работы</h2>
                        <p><?= nl2br(htmlspecialchars($profile['experience_description'])) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Стоимость -->
                <?php if ($profile['hourly_rate_min'] || $profile['hourly_rate_max']): ?>
                    <div class="profile-section">
                        <h2>Стоимость</h2>
                        <p class="profile-rate">
                            <?php if ($profile['hourly_rate_min'] && $profile['hourly_rate_max']): ?>
                                <?= number_format($profile['hourly_rate_min'], 0, '', ' ') ?> – <?= number_format($profile['hourly_rate_max'], 0, '', ' ') ?> ₽/час
                            <?php elseif ($profile['hourly_rate_min']): ?>
                                от <?= number_format($profile['hourly_rate_min'], 0, '', ' ') ?> ₽/час
                            <?php else: ?>
                                до <?= number_format($profile['hourly_rate_max'], 0, '', ' ') ?> ₽/час
                            <?php endif; ?>
                        </p>
                        <p class="text-muted">Оплата договорная. Возможна оплата отзывом вместо денег.</p>
                    </div>
                <?php endif; ?>

                <!-- Кнопка записи -->
                <div class="profile-cta">
                    <a href="/client/cases/create" class="btn btn-primary btn-lg">Создать запрос к специалисту</a>
                    <p class="text-muted" style="margin-top:0.5rem; font-size:0.875rem;">
                        Опишите свой запрос — специалист откликнется
                    </p>
                </div>

                <!-- Активность -->
                <div class="profile-section">
                    <h2>Активность на платформе</h2>
                    <div class="stats-row">
                        <div class="stat-item">
                            <span class="stat-value"><?= $stats['response_count'] ?></span>
                            <span class="stat-label">откликов на кейсы</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?= $stats['sessions_attended_month'] ?></span>
                            <span class="stat-label">интервизий за месяц</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?= date('d.m.Y', strtotime($profile['registered_at'])) ?></span>
                            <span class="stat-label">на платформе с</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="profile-actions">
            <a href="/psychologists" class="btn btn-outline">← Вернуться к списку</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
