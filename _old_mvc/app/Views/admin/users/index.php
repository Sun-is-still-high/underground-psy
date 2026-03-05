<?php
$title = 'Пользователи — Админ';
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Пользователи</h1>
        <a href="/admin" class="btn btn-outline btn-sm">← Назад в панель</a>
    </div>

    <!-- Фильтры -->
    <form method="GET" action="/admin/users" class="filter-form" style="margin-bottom:24px;">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
               placeholder="Поиск по имени или email" class="form-control" style="max-width:280px;">
        <select name="role" class="form-control" style="max-width:160px;">
            <option value="">Все роли</option>
            <option value="CLIENT"       <?= $role === 'CLIENT'       ? 'selected' : '' ?>>Клиенты</option>
            <option value="PSYCHOLOGIST" <?= $role === 'PSYCHOLOGIST' ? 'selected' : '' ?>>Психологи</option>
            <option value="ADMIN"        <?= $role === 'ADMIN'        ? 'selected' : '' ?>>Администраторы</option>
        </select>
        <button type="submit" class="btn btn-primary">Найти</button>
        <?php if ($search || $role): ?>
            <a href="/admin/users" class="btn btn-outline">Сбросить</a>
        <?php endif; ?>
    </form>

    <?php if (empty($users)): ?>
        <div class="empty-state"><p>Пользователи не найдены.</p></div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Статус</th>
                        <th>Зарегистрирован</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr class="<?= $u['is_blocked'] ? 'row--blocked' : '' ?>">
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php
                            $roleLabels = ['CLIENT' => 'Клиент', 'PSYCHOLOGIST' => 'Психолог', 'ADMIN' => 'Админ'];
                            echo htmlspecialchars($roleLabels[$u['role']] ?? $u['role']);
                            ?>
                        </td>
                        <td>
                            <?php if ($u['is_blocked']): ?>
                                <span class="badge badge-error" title="<?= htmlspecialchars($u['blocked_reason'] ?? '') ?>">
                                    Заблокирован
                                </span>
                            <?php else: ?>
                                <span class="badge badge-success">Активен</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['is_blocked']): ?>
                                <form action="/admin/users/<?= $u['id'] ?>/unblock" method="POST" style="display:inline;">
                                    <button type="submit" class="btn btn-outline btn-sm">Разблокировать</button>
                                </form>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="showBlockForm(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>')">
                                    Заблокировать
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Модалка блокировки -->
<div id="blockModal" class="modal" style="display:none;">
    <div class="modal-overlay" onclick="closeBlockModal()"></div>
    <div class="modal-box">
        <h3>Заблокировать пользователя</h3>
        <p id="blockModalName" style="color:#6b7280;margin-bottom:16px;"></p>
        <form id="blockForm" method="POST">
            <div class="form-group">
                <label for="blockReason">Причина (необязательно)</label>
                <textarea id="blockReason" name="reason" class="form-control" rows="3"
                          placeholder="Укажите причину блокировки..."></textarea>
            </div>
            <div style="display:flex;gap:8px;margin-top:16px;">
                <button type="submit" class="btn btn-danger">Заблокировать</button>
                <button type="button" class="btn btn-outline" onclick="closeBlockModal()">Отмена</button>
            </div>
        </form>
    </div>
</div>

<script>
function showBlockForm(userId, userName) {
    document.getElementById('blockForm').action = '/admin/users/' + userId + '/block';
    document.getElementById('blockModalName').textContent = userName;
    document.getElementById('blockReason').value = '';
    document.getElementById('blockModal').style.display = 'flex';
}
function closeBlockModal() {
    document.getElementById('blockModal').style.display = 'none';
}
</script>

<style>
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th,
.admin-table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border, #e5e7eb); font-size: .9rem; }
.admin-table th { background: var(--bg-subtle, #f9fafb); font-weight: 600; }
.row--blocked td { color: #9ca3af; }
.table-wrapper { overflow-x: auto; }
.filter-form { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.btn-danger { background: #ef4444; color: #fff; border: none; }
.btn-danger:hover { background: #dc2626; }

.modal { position: fixed; inset: 0; z-index: 1000; display: flex; align-items: center; justify-content: center; }
.modal-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.4); }
.modal-box { position: relative; background: #fff; border-radius: 10px; padding: 28px; width: 100%; max-width: 420px; box-shadow: 0 8px 32px rgba(0,0,0,.16); }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>
