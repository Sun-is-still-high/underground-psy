<?php
$title = 'Медицинским учреждениям — Underground Psy';
ob_start();
?>

<div class="container">
    <div class="landing-hero">
        <h1>Медицинским учреждениям</h1>
        <p class="landing-subtitle">
            Платформа для клиник, психологических центров и медицинских организаций.<br>
            Найдите специалистов или разместите профили своих сотрудников.
        </p>
        <a href="/psychologists" class="btn btn-primary btn-lg">Каталог специалистов</a>
    </div>

    <div class="landing-features">
        <div class="feature-card">
            <div class="feature-icon">🏥</div>
            <h3>Для клиник</h3>
            <p>Размещение профилей штатных психологов и психотерапевтов на платформе для расширения охвата пациентов.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🧠</div>
            <h3>Для психологических центров</h3>
            <p>Продвижение специалистов центра, публикация групповых программ и тренингов.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">👨‍⚕️</div>
            <h3>Для врачей-психотерапевтов</h3>
            <p>Отдельный профиль с указанием медицинской специализации и методов лечения.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📋</div>
            <h3>Верификация</h3>
            <p>Все специалисты проходят верификацию документов об образовании. Работаем только с подтверждёнными профессионалами.</p>
        </div>
    </div>

    <div class="landing-advantages">
        <h2>Преимущества платформы</h2>
        <ul class="advantages-list">
            <li>Размещение бесплатно — без комиссий и платы за регистрацию</li>
            <li>Ручная проверка дипломов и документов об образовании</li>
            <li>Группы поддержки, семинары и тренинги в одном месте</li>
            <li>Фильтрация по специализации, языку, городу и формату работы</li>
            <li>Анонимные вопросы от пациентов — прямо на платформе</li>
        </ul>
    </div>

    <div class="landing-cta">
        <h2>Хотите сотрудничать?</h2>
        <p>Зарегистрируйтесь как специалист или напишите нам для обсуждения корпоративного размещения.</p>
        <div class="cta-buttons">
            <a href="/register" class="btn btn-primary btn-lg">Зарегистрироваться</a>
            <a href="/ask" class="btn btn-outline btn-lg">Задать вопрос</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
