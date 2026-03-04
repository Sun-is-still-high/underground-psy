<?php
$title = htmlspecialchars($profile['psychologist_name']) . ' — Психолог — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="profile-page">
        <!-- Заголовок -->
        <div class="profile-header">
            <div class="profile-avatar-lg"><?= mb_substr($profile['psychologist_name'], 0, 1) ?></div>
            <h1><?= htmlspecialchars($profile['psychologist_name']) ?></h1>

            <?php if (!empty($specializations)): ?>
                <div class="profile-specializations">
                    <?php foreach ($specializations as $spec): ?>
                        <span class="badge badge-primary"><?= htmlspecialchars($spec['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Предупреждение -->
        <div class="warning-box">
            <strong>Обратите внимание:</strong> это начинающий специалист.
            Платформа Underground Psy создана для развития молодых психологов.
            Вы можете запросить подтверждение образования у специалиста лично.
        </div>

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

            <!-- Статистика -->
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

        <div class="profile-actions">
            <a href="/psychologists" class="btn btn-outline">← Вернуться к списку</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
