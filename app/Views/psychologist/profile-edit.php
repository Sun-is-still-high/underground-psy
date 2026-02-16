<?php
$title = 'Редактирование профиля - Underground Psy';
$old = \Core\Session::getFlash('old') ?? [];
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Редактирование профиля</h1>
        <p class="page-subtitle">Заполните информацию о себе для клиентов</p>
    </div>

    <form action="/psychologist/profile/update" method="POST" class="profile-form">
        <!-- О себе -->
        <div class="form-group">
            <label for="bio">О себе <span class="required">*</span></label>
            <textarea name="bio" id="bio" class="form-control" rows="5"
                      placeholder="Расскажите о себе, своём подходе к работе..."
            ><?= htmlspecialchars($old['bio'] ?? $profile['bio'] ?? '') ?></textarea>
            <small class="form-hint">Обязательно для публикации профиля</small>
        </div>

        <!-- Методы -->
        <div class="form-group">
            <label for="methods_description">Методы и подходы</label>
            <textarea name="methods_description" id="methods_description" class="form-control" rows="4"
                      placeholder="Какие методы вы используете? (КПТ, психоанализ, гештальт и т.д.)"
            ><?= htmlspecialchars($old['methods_description'] ?? $profile['methods_description'] ?? '') ?></textarea>
        </div>

        <!-- Специализации -->
        <div class="form-group">
            <label>Специализации</label>
            <div class="checkbox-group">
                <?php
                $selectedIds = array_column($specializations, 'id');
                foreach ($problemTypes as $type):
                ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="specializations[]" value="<?= $type['id'] ?>"
                               <?= in_array($type['id'], $selectedIds) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($type['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <small class="form-hint">Выберите типы проблем, с которыми вы работаете</small>
        </div>

        <!-- Образование -->
        <div class="form-group">
            <label for="education">Образование</label>
            <textarea name="education" id="education" class="form-control" rows="3"
                      placeholder="Укажите образование, курсы, сертификаты..."
            ><?= htmlspecialchars($old['education'] ?? $profile['education'] ?? '') ?></textarea>
        </div>

        <!-- Опыт -->
        <div class="form-group">
            <label for="experience_description">Опыт работы</label>
            <textarea name="experience_description" id="experience_description" class="form-control" rows="3"
                      placeholder="Опишите ваш опыт работы..."
            ><?= htmlspecialchars($old['experience_description'] ?? $profile['experience_description'] ?? '') ?></textarea>
        </div>

        <!-- Ставка -->
        <div class="form-row">
            <div class="form-group">
                <label for="hourly_rate_min">Минимальная ставка (₽/час)</label>
                <input type="number" name="hourly_rate_min" id="hourly_rate_min"
                       class="form-control" step="100" min="0"
                       value="<?= htmlspecialchars($old['hourly_rate_min'] ?? $profile['hourly_rate_min'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="hourly_rate_max">Максимальная ставка (₽/час)</label>
                <input type="number" name="hourly_rate_max" id="hourly_rate_max"
                       class="form-control" step="100" min="0"
                       value="<?= htmlspecialchars($old['hourly_rate_max'] ?? $profile['hourly_rate_max'] ?? '') ?>">
            </div>
        </div>
        <small class="form-hint">Оставьте пустым, если оплата договорная или за отзыв</small>

        <!-- Публикация -->
        <div class="form-group" style="margin-top: 1.5rem;">
            <label class="checkbox-label">
                <input type="checkbox" name="is_published" value="1"
                       <?= ($old['is_published'] ?? $profile['is_published'] ?? 0) ? 'checked' : '' ?>>
                Опубликовать профиль (сделать видимым для клиентов)
            </label>
        </div>

        <!-- Кнопки -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Сохранить профиль</button>
            <a href="/dashboard" class="btn btn-outline">Отмена</a>
            <?php if ($profile['is_published']): ?>
                <a href="/psychologists/<?= $profile['id'] ?>" class="btn btn-outline">Посмотреть профиль</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
