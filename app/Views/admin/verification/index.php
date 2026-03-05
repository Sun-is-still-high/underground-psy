<?php
$title = 'Верификация дипломов — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Верификация дипломов</h1>
        <p class="page-subtitle">Психологи, загрузившие документы об образовании</p>
    </div>

    <?php if (empty($profiles)): ?>
        <div class="empty-state">
            <p>Нет загруженных дипломов для проверки.</p>
        </div>
    <?php else: ?>
        <div class="verification-list">
            <?php foreach ($profiles as $p): ?>
                <div class="verification-item <?= $p['diploma_verified'] ? 'verified' : 'pending' ?>">
                    <div class="verification-info">
                        <strong><?= htmlspecialchars($p['name']) ?></strong>
                        <span class="text-muted"><?= htmlspecialchars($p['email']) ?></span>
                        <a href="/psychologists/<?= $p['profile_id'] ?>" target="_blank" class="btn btn-outline btn-sm">Профиль</a>
                    </div>

                    <div class="verification-diploma">
                        <?php
                        $ext = strtolower(pathinfo($p['diploma_scan_url'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg','jpeg','png','webp'])):
                        ?>
                            <a href="<?= htmlspecialchars($p['diploma_scan_url']) ?>" target="_blank">
                                <img src="<?= htmlspecialchars($p['diploma_scan_url']) ?>"
                                     alt="Диплом" class="diploma-preview">
                            </a>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($p['diploma_scan_url']) ?>" target="_blank" class="btn btn-outline">
                                Открыть PDF
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="verification-actions">
                        <?php if ($p['diploma_verified']): ?>
                            <span class="badge badge-success">Верифицирован</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Ожидает проверки</span>
                            <form action="/admin/verification/<?= $p['profile_id'] ?>/approve" method="POST" style="display:inline;">
                                <button type="submit" class="btn btn-primary btn-sm">Подтвердить</button>
                            </form>
                            <form action="/admin/verification/<?= $p['profile_id'] ?>/reject" method="POST" style="display:inline;">
                                <button type="submit" class="btn btn-outline btn-sm"
                                        onclick="return confirm('Отклонить и удалить скан?')">Отклонить</button>
                            </form>
                        <?php endif; ?>
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
