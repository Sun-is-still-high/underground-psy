<?php
$title = 'Группы интервизий - Админка';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Группы интервизий</h1>
        <a href="/admin/intervision/groups/create" class="btn btn-primary">Создать группу</a>
    </div>

    <?php if (empty($groups)): ?>
        <div class="empty-state">
            <p>Групп пока нет. Создайте первую группу для начала работы.</p>
        </div>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Участников</th>
                    <th>Макс.</th>
                    <th>Создатель</th>
                    <th>Создана</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($group['name']) ?></strong></td>
                    <td><?= $group['participants_count'] ?></td>
                    <td><?= $group['max_participants'] ?></td>
                    <td><?= htmlspecialchars($group['creator_name']) ?></td>
                    <td><?= date('d.m.Y', strtotime($group['created_at'])) ?></td>
                    <td>
                        <a href="/admin/intervision/groups/<?= $group['id'] ?>" class="btn btn-sm">Открыть</a>
                        <a href="/admin/intervision/groups/<?= $group['id'] ?>/edit" class="btn btn-sm btn-outline">Редактировать</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../../layouts/main.php';
?>
