<?php
$title = 'Редактирование профиля — Underground Psy';
$old = \Core\Session::getFlash('old') ?? [];

$targetSelected = array_filter(explode(',', $profile['target_audience'] ?? ''));
$targetOptions  = [
    'ADULTS'   => 'Взрослые',
    'COUPLES'  => 'Пары и семьи',
    'TEENS'    => 'Подростки',
    'CHILDREN' => 'Дети и родители',
    'BUSINESS' => 'Бизнес / корпоративные клиенты',
];

ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Редактирование профиля</h1>
        <p class="page-subtitle">Заполните информацию о себе для клиентов</p>
    </div>

    <!-- Заполненность профиля -->
    <?php if (!empty($completeness)): ?>
        <div class="completeness-widget">
            <div class="completeness-header">
                <span>Заполненность профиля</span>
                <strong><?= $completeness['percent'] ?>%</strong>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $completeness['percent'] ?>%"></div>
            </div>
            <?php if (!empty($completeness['missing'])): ?>
                <ul class="completeness-tips">
                    <?php foreach ($completeness['missing'] as $tip): ?>
                        <li><?= htmlspecialchars($tip) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="completeness-done">Отличная работа! Профиль заполнен полностью.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Подтверждение актуальности цен -->
    <?php if ($needsConfirm): ?>
        <div class="alert alert-warning" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
            <span>Пожалуйста, подтвердите, что стоимость ваших сессий актуальна. Это требуется раз в 30 дней.</span>
            <form action="/psychologist/profile/confirm-price" method="POST" style="flex-shrink:0;">
                <button type="submit" class="btn btn-primary btn-sm">Подтвердить цены</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Форма профиля -->
    <form action="/psychologist/profile/update" method="POST" enctype="multipart/form-data" class="profile-form">

        <!-- === ФОТО ПРОФИЛЯ === -->
        <div class="form-section">
            <h2 class="form-section-title">Фото профиля</h2>
            <div class="photo-upload-row">
                <div class="current-photo">
                    <?php if (!empty($profile['photo_url'])): ?>
                        <img src="<?= htmlspecialchars($profile['photo_url']) ?>" alt="Фото профиля" class="profile-photo-preview">
                    <?php else: ?>
                        <div class="profile-avatar-lg"><?= mb_substr($user['name'], 0, 1) ?></div>
                    <?php endif; ?>
                </div>
                <div class="photo-upload-control">
                    <label for="photo" class="btn btn-outline" style="cursor:pointer;">Загрузить фото</label>
                    <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp" style="display:none;">
                    <small class="form-hint">JPG, PNG или WEBP, до 5 МБ</small>
                </div>
            </div>
        </div>

        <!-- === ЛИЧНАЯ ИНФОРМАЦИЯ === -->
        <div class="form-section">
            <h2 class="form-section-title">Личная информация</h2>

            <div class="form-group">
                <label for="gender">Пол</label>
                <select name="gender" id="gender" class="form-control">
                    <option value="">Не указан</option>
                    <option value="MALE"   <?= ($old['gender'] ?? $user['gender'] ?? '') === 'MALE'   ? 'selected' : '' ?>>Мужской</option>
                    <option value="FEMALE" <?= ($old['gender'] ?? $user['gender'] ?? '') === 'FEMALE' ? 'selected' : '' ?>>Женский</option>
                </select>
            </div>
        </div>

        <!-- === О СЕБЕ === -->
        <div class="form-section">
            <h2 class="form-section-title">О себе</h2>

            <div class="form-group">
                <label for="bio">О себе <span class="required">*</span></label>
                <textarea name="bio" id="bio" class="form-control" rows="5"
                          placeholder="Расскажите о себе, своём подходе к работе..."
                ><?= htmlspecialchars($old['bio'] ?? $profile['bio'] ?? '') ?></textarea>
                <small class="form-hint">Обязательно для публикации профиля</small>
            </div>

            <div class="form-group">
                <label for="methods_description">Методы и подходы</label>
                <textarea name="methods_description" id="methods_description" class="form-control" rows="4"
                          placeholder="КПТ, гештальт, психоанализ, EMDR и т.д."
                ><?= htmlspecialchars($old['methods_description'] ?? $profile['methods_description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="education">Образование</label>
                <textarea name="education" id="education" class="form-control" rows="3"
                          placeholder="Вуз, специальность, год окончания, дополнительное образование..."
                ><?= htmlspecialchars($old['education'] ?? $profile['education'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="experience_description">Опыт работы</label>
                <textarea name="experience_description" id="experience_description" class="form-control" rows="3"
                          placeholder="Опишите ваш опыт, продолжительность практики..."
                ><?= htmlspecialchars($old['experience_description'] ?? $profile['experience_description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- === СПЕЦИАЛИЗАЦИИ === -->
        <div class="form-section">
            <h2 class="form-section-title">Специализации</h2>
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
        </div>

        <!-- === ЦЕЛЕВАЯ АУДИТОРИЯ === -->
        <div class="form-section">
            <h2 class="form-section-title">Целевая аудитория</h2>
            <div class="checkbox-group">
                <?php foreach ($targetOptions as $val => $label): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="target_audience[]" value="<?= $val ?>"
                               <?= in_array($val, $targetSelected) ? 'checked' : '' ?>>
                        <?= $label ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- === ФОРМАТ РАБОТЫ === -->
        <div class="form-section">
            <h2 class="form-section-title">Формат и стоимость</h2>

            <div class="form-group">
                <label for="work_format">Формат работы</label>
                <select name="work_format" id="work_format" class="form-control" onchange="toggleCityField(this.value)">
                    <option value="ONLINE"  <?= ($old['work_format'] ?? $profile['work_format'] ?? 'ONLINE') === 'ONLINE'  ? 'selected' : '' ?>>Только онлайн</option>
                    <option value="OFFLINE" <?= ($old['work_format'] ?? $profile['work_format'] ?? '') === 'OFFLINE' ? 'selected' : '' ?>>Только офлайн</option>
                    <option value="BOTH"    <?= ($old['work_format'] ?? $profile['work_format'] ?? '') === 'BOTH'    ? 'selected' : '' ?>>Онлайн и офлайн</option>
                </select>
            </div>

            <div class="form-group" id="city-field"
                 style="<?= in_array($profile['work_format'] ?? 'ONLINE', ['OFFLINE','BOTH']) ? '' : 'display:none' ?>">
                <label for="city">Город <span class="required">*</span></label>
                <input type="text" name="city" id="city" class="form-control"
                       placeholder="Например: Минск"
                       value="<?= htmlspecialchars($old['city'] ?? $profile['city'] ?? '') ?>">
                <small class="form-hint">Город, в котором принимаете очно</small>
            </div>

            <div class="form-group">
                <label for="languages">Языки приёма</label>
                <input type="text" name="languages" id="languages" class="form-control"
                       placeholder="Русский, Белорусский, Английский"
                       value="<?= htmlspecialchars($old['languages'] ?? $profile['languages'] ?? '') ?>">
                <small class="form-hint">Перечислите языки через запятую</small>
            </div>

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
        </div>

        <!-- === РЕЖИМ ПРОФИЛЯ === -->
        <div class="form-section">
            <h2 class="form-section-title">Видимость профиля</h2>

            <div class="form-group">
                <label for="profile_mode">Режим отображения</label>
                <select name="profile_mode" id="profile_mode" class="form-control">
                    <option value="FULL"     <?= ($old['profile_mode'] ?? $profile['profile_mode'] ?? 'FULL') === 'FULL'     ? 'selected' : '' ?>>Полный — цена и возможность связаться видны клиентам</option>
                    <option value="REGISTRY" <?= ($old['profile_mode'] ?? $profile['profile_mode'] ?? '') === 'REGISTRY' ? 'selected' : '' ?>>Реестр — только имя и специализации (без цены и контактов)</option>
                </select>
                <small class="form-hint">В режиме «Реестр» вы присутствуете в каталоге, но клиенты не видят цену и не могут написать</small>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_published" value="1"
                           <?= ($old['is_published'] ?? $profile['is_published'] ?? 0) ? 'checked' : '' ?>>
                    Опубликовать профиль (сделать видимым для клиентов)
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Сохранить профиль</button>
            <a href="/dashboard" class="btn btn-outline">Отмена</a>
            <?php if ($profile['is_published']): ?>
                <a href="/psychologists/<?= $profile['id'] ?>" class="btn btn-outline" target="_blank">Посмотреть профиль</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- === ВЕРИФИКАЦИЯ ДИПЛОМА (отдельная форма) === -->
    <div class="form-section" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
        <h2 class="form-section-title">Верификация диплома</h2>

        <?php if ($profile['diploma_verified']): ?>
            <div class="alert alert-success">
                Ваш диплом верифицирован администратором. На профиле отображается значок «Верифицирован».
            </div>
        <?php elseif (!empty($profile['diploma_scan_url'])): ?>
            <div class="alert" style="background:#fff8e1; border-left: 4px solid #ffc107; padding:1rem; border-radius:6px;">
                Скан диплома загружен и ожидает проверки администратора.
            </div>
        <?php else: ?>
            <p class="text-muted">Загрузите скан диплома, чтобы получить значок верификации на вашем профиле.</p>
        <?php endif; ?>

        <form action="/psychologist/diploma/upload" method="POST" enctype="multipart/form-data" style="margin-top: 1rem;">
            <div class="form-group">
                <label for="diploma">Скан диплома / сертификата</label>
                <input type="file" name="diploma" id="diploma" class="form-control"
                       accept="image/jpeg,image/png,application/pdf">
                <small class="form-hint">JPG, PNG или PDF, до 10 МБ</small>
            </div>
            <button type="submit" class="btn btn-outline">Загрузить диплом</button>
        </form>
    </div>
</div>

<script>
function toggleCityField(value) {
    const el = document.getElementById('city-field');
    el.style.display = (value === 'OFFLINE' || value === 'BOTH') ? '' : 'none';
}

document.getElementById('photo').addEventListener('change', function() {
    const label = document.querySelector('label[for="photo"]');
    label.textContent = this.files[0] ? this.files[0].name : 'Загрузить фото';
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
