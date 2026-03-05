<?php
$title = 'Спросить психолога — Underground Psy';
$old = \Core\Session::getFlash('old') ?? [];
ob_start();
?>

<div class="container">
    <div class="page-header">
        <h1>Спросить психолога</h1>
        <p class="page-subtitle">
            Задайте вопрос анонимно — психологи платформы ответят бесплатно.<br>
            Ответ будет опубликован в открытом разделе и отправлен на ваш email.
        </p>
    </div>

    <div class="two-col-layout">
        <div class="col-main">
            <div class="card">
                <form action="/ask" method="POST">
                    <div class="form-group">
                        <label for="author_name">Ваше имя <span class="required">*</span></label>
                        <input type="text" name="author_name" id="author_name" class="form-control"
                               placeholder="Как к вам обращаться?"
                               value="<?= htmlspecialchars($old['author_name'] ?? '') ?>">
                        <small class="form-hint">Можно использовать псевдоним</small>
                    </div>

                    <div class="form-group">
                        <label for="author_email">Email <span class="required">*</span></label>
                        <input type="email" name="author_email" id="author_email" class="form-control"
                               placeholder="example@mail.com"
                               value="<?= htmlspecialchars($old['author_email'] ?? '') ?>">
                        <small class="form-hint">Адрес не публикуется — только для получения уведомления об ответе</small>
                    </div>

                    <div class="form-group">
                        <label for="question">Ваш вопрос <span class="required">*</span></label>
                        <textarea name="question" id="question" class="form-control" rows="6"
                                  placeholder="Опишите вашу ситуацию или задайте вопрос..."><?= htmlspecialchars($old['question'] ?? '') ?></textarea>
                        <small class="form-hint">Минимум 20 символов</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Отправить вопрос</button>
                        <a href="/questions" class="btn btn-outline">Читать ответы</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-aside">
            <div class="info-box">
                <h3>Как это работает</h3>
                <ol class="steps-list">
                    <li>Вы задаёте вопрос — регистрация не нужна</li>
                    <li>Психологи платформы видят вопрос и могут ответить</li>
                    <li>Ответ публикуется в открытом разделе</li>
                    <li>Вы получаете уведомление на email</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
