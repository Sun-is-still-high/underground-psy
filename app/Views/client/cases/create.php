<?php
$title = 'Создать запрос - Underground Psy';
$old = \Core\Session::get('old') ?? [];
\Core\Session::forget('old');
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Создать запрос на помощь</h1>
    </div>

    <div class="form-container">
        <p class="subtitle">Опишите вашу ситуацию, и психологи смогут откликнуться на ваш запрос</p>

        <form action="/client/cases" method="POST" class="auth-form">
            <div class="form-group">
                <label for="problem_type_id">Тип проблемы *</label>
                <select name="problem_type_id" id="problem_type_id" class="form-control" required>
                    <option value="">-- Выберите тип проблемы --</option>
                    <?php foreach ($problemTypes as $type): ?>
                        <option value="<?= $type['id'] ?>"
                            <?= ($old['problem_type_id'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="title">Краткое описание проблемы *</label>
                <input type="text" name="title" id="title" class="form-control"
                       placeholder="Например: Не могу справиться с тревогой на работе"
                       value="<?= htmlspecialchars($old['title'] ?? '') ?>" required maxlength="255">
                <small>Это увидят психологи в списке кейсов</small>
            </div>

            <div class="form-group">
                <label for="description">Подробное описание *</label>
                <textarea name="description" id="description" class="form-control" rows="6"
                          placeholder="Расскажите подробнее о вашей ситуации, как давно это происходит, что вы уже пробовали..."
                          required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                <small>Чем подробнее вы опишете ситуацию, тем точнее психолог сможет помочь</small>
            </div>

            <div class="form-group">
                <label for="budget_type">Формат оплаты</label>
                <select name="budget_type" id="budget_type" class="form-control">
                    <option value="NEGOTIABLE" <?= ($old['budget_type'] ?? '') == 'NEGOTIABLE' ? 'selected' : '' ?>>
                        Договорная
                    </option>
                    <option value="PAID" <?= ($old['budget_type'] ?? '') == 'PAID' ? 'selected' : '' ?>>
                        Платно (укажу сумму)
                    </option>
                    <option value="REVIEW" <?= ($old['budget_type'] ?? '') == 'REVIEW' ? 'selected' : '' ?>>
                        Готов оплатить отзывом
                    </option>
                </select>
            </div>

            <div class="form-group" id="budget_amount_group" style="display: none;">
                <label for="budget_amount">Бюджет (руб.)</label>
                <input type="number" name="budget_amount" id="budget_amount" class="form-control"
                       placeholder="Например: 3000" min="0" step="100"
                       value="<?= htmlspecialchars($old['budget_amount'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_anonymous" value="1"
                        <?= ($old['is_anonymous'] ?? '') == '1' ? 'checked' : '' ?>>
                    <span>Анонимный запрос</span>
                </label>
                <small>Психологи не увидят ваше имя, пока вы не примете их отклик</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Опубликовать запрос</button>
                <a href="/client/cases" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('budget_type').addEventListener('change', function() {
    const budgetGroup = document.getElementById('budget_amount_group');
    budgetGroup.style.display = this.value === 'PAID' ? 'block' : 'none';
});
// Проверяем при загрузке
if (document.getElementById('budget_type').value === 'PAID') {
    document.getElementById('budget_amount_group').style.display = 'block';
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>
