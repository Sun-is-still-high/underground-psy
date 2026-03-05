<?php
$title = 'Психология для бизнеса — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="landing-hero">
        <h1>Психология для бизнеса</h1>
        <p class="landing-subtitle">
            Поддержка собственников, руководителей и команд.<br>
            Работаем с лидерством, мотивацией, коммуникацией и конфликтами.
        </p>
        <a href="/psychologists?specialization=" class="btn btn-primary btn-lg">Найти специалиста</a>
    </div>

    <div class="landing-features">
        <div class="feature-card">
            <div class="feature-icon">👔</div>
            <h3>Для руководителей</h3>
            <p>Работа с синдромом самозванника, управленческим стрессом, принятием сложных решений и эмоциональным выгоранием.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🤝</div>
            <h3>Для команд</h3>
            <p>Улучшение коммуникации, разрешение конфликтов, повышение сплочённости и мотивации сотрудников.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📈</div>
            <h3>Для HR</h3>
            <p>Консультации при подборе персонала, оценка психологического климата, профилактика выгорания.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🎯</div>
            <h3>Для собственников</h3>
            <p>Личная эффективность, work-life balance, принятие стратегических решений под давлением.</p>
        </div>
    </div>

    <div class="landing-formats">
        <h2>Форматы работы</h2>
        <div class="format-list">
            <div class="format-item">
                <strong>Индивидуальные консультации</strong>
                <p>Сессии один на один с руководителем или ключевым сотрудником</p>
            </div>
            <div class="format-item">
                <strong>Групповые сессии и тренинги</strong>
                <p>Для команд от 5 до 30 человек, онлайн или офлайн</p>
            </div>
            <div class="format-item">
                <strong>Корпоративные программы</strong>
                <p>Регулярное сопровождение сотрудников на основе запроса компании</p>
            </div>
        </div>
    </div>

    <div class="landing-cta">
        <h2>Готовы начать?</h2>
        <p>Найдите специалиста с опытом работы с бизнес-аудиторией или задайте вопрос анонимно.</p>
        <div class="cta-buttons">
            <a href="/psychologists" class="btn btn-primary btn-lg">Каталог специалистов</a>
            <a href="/ask" class="btn btn-outline btn-lg">Задать вопрос бесплатно</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
