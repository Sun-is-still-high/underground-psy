@extends('layouts.main')

@section('title', 'Админ-панель — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Админ-панель</h1>
        <p class="page-subtitle">Управление платформой Underground Psy</p>
    </div>

    {{-- Статистика --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $byRole['CLIENT'] }}</div>
            <div class="stat-label">Клиентов</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $byRole['PSYCHOLOGIST'] }}</div>
            <div class="stat-label">Психологов</div>
        </div>
        <div class="stat-card stat-card--highlight">
            <div class="stat-value">+{{ $newUsers }}</div>
            <div class="stat-label">Новых за 30 дней</div>
        </div>
        <div class="stat-card {{ $unansweredQuestions > 0 ? 'stat-card--warning' : '' }}">
            <div class="stat-value">{{ $unansweredQuestions }}</div>
            <div class="stat-label">Вопросов без ответа</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $upcomingEvents }} / {{ $totalEvents }}</div>
            <div class="stat-label">Предстоящих / всего мероприятий</div>
        </div>
        @if ($blockedUsers > 0)
        <div class="stat-card stat-card--warning">
            <div class="stat-value">{{ $blockedUsers }}</div>
            <div class="stat-label">Заблокировано</div>
        </div>
        @endif
    </div>

    {{-- Разделы управления --}}
    <div class="admin-sections">
        <h2>Разделы</h2>
        <div class="admin-nav-grid">
            <a href="{{ route('admin.users.index') }}" class="admin-nav-card">
                <div class="admin-nav-card__title">Пользователи</div>
                <div class="admin-nav-card__desc">Список, блокировка, поиск</div>
            </a>
            <a href="{{ route('admin.intervision.groups') }}" class="admin-nav-card">
                <div class="admin-nav-card__title">Интервизии</div>
                <div class="admin-nav-card__desc">Группы и сессии</div>
            </a>
            <a href="{{ route('admin.tasks.index') }}" class="admin-nav-card {{ $pendingTasks > 0 ? 'admin-nav-card--alert' : '' }}">
                <div class="admin-nav-card__title">
                    Задания (тройки)
                    @if($pendingTasks > 0)
                        <span class="badge badge-warning">{{ $pendingTasks }}</span>
                    @endif
                </div>
                <div class="admin-nav-card__desc">Модерация предложенных заданий</div>
            </a>
            @if ($pendingVerification > 0)
            <div class="admin-nav-card admin-nav-card--alert">
                <div class="admin-nav-card__title">
                    Верификация дипломов
                    <span class="badge badge-warning">{{ $pendingVerification }}</span>
                </div>
                <div class="admin-nav-card__desc">Ожидают проверки документов</div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 40px;
}
.stat-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}
.stat-card--highlight { border-color: #6366f1; }
.stat-card--warning   { border-color: #f59e0b; }
.stat-value { font-size: 2rem; font-weight: 700; line-height: 1; margin-bottom: 6px; }
.stat-label { font-size: 0.85rem; color: #6b7280; }

.admin-sections h2 { margin-bottom: 16px; }
.admin-nav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
}
.admin-nav-card {
    display: block;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    text-decoration: none;
    color: inherit;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.admin-nav-card:hover   { border-color: #6366f1; box-shadow: 0 2px 8px rgba(99,102,241,.12); }
.admin-nav-card--alert  { border-color: #f59e0b; }
.admin-nav-card__title  { font-weight: 600; margin-bottom: 4px; }
.admin-nav-card__desc   { font-size: 0.85rem; color: #6b7280; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: .75rem; font-weight: 600; margin-left: 6px; }
.badge-warning { background: #fef3c7; color: #92400e; }
</style>
@endsection
