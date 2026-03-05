@extends('layouts.main')

@section('title', 'Личный кабинет - Underground Psy')

@section('content')
<div class="container">
    <div class="dashboard">
        <h1>Личный кабинет</h1>

        <div class="user-info-card">
            <h2>Информация о профиле</h2>
            <div class="info-row"><span class="label">Имя:</span><span class="value">{{ $user->name }}</span></div>
            <div class="info-row"><span class="label">Email:</span><span class="value">{{ $user->email }}</span></div>
            <div class="info-row">
                <span class="label">Роль:</span>
                <span class="value">
                    @php $roleNames = ['CLIENT' => 'Клиент', 'PSYCHOLOGIST' => 'Психолог', 'ADMIN' => 'Администратор']; @endphp
                    {{ $roleNames[$user->role] ?? $user->role }}
                </span>
            </div>
            <div class="info-row">
                <span class="label">Дата регистрации:</span>
                <span class="value">{{ $user->created_at->format('d.m.Y') }}</span>
            </div>
        </div>

        @if ($profileWarning)
            <div class="alert alert-warning">
                {{ $profileWarning }}
                <a href="{{ route('psychologist.profile.edit') }}" class="btn btn-primary btn-sm" style="margin-left: 1rem;">Заполнить профиль</a>
            </div>
        @endif

        @if ($user->isClient())
            <div class="dashboard-section">
                <h2>Мои действия</h2>
                <div class="action-cards">
                    <div class="action-card">
                        <h3>Создать запрос</h3>
                        <p>Опишите проблему — психологи откликнутся</p>
                        <a href="{{ route('client.cases.create') }}" class="btn btn-primary">Создать запрос</a>
                    </div>
                    <div class="action-card">
                        <h3>Мои запросы</h3>
                        <p>Просмотр запросов и откликов</p>
                        <a href="{{ route('client.cases.index') }}" class="btn btn-outline">Перейти</a>
                    </div>
                </div>
            </div>
        @elseif ($user->isPsychologist())
            <div class="dashboard-section">
                <h2>Мои действия</h2>
                <div class="action-cards">
                    <div class="action-card">
                        <h3>Мой профиль</h3>
                        <p>Заполните профиль для клиентов</p>
                        <a href="{{ route('psychologist.profile.edit') }}" class="btn btn-primary">Редактировать</a>
                    </div>
                    <div class="action-card">
                        <h3>Поиск кейсов</h3>
                        <p>Найти клиентов по типу проблемы</p>
                        <a href="{{ route('psychologist.cases.index') }}" class="btn btn-outline">Найти кейсы</a>
                    </div>
                    <div class="action-card">
                        <h3>Мои интервизии</h3>
                        <p>Статус посещения и группы</p>
                        <a href="{{ route('psychologist.intervisions') }}" class="btn btn-outline">Перейти</a>
                    </div>
                </div>
            </div>
        @elseif ($user->isAdmin())
            <div class="dashboard-section">
                <h2>Администрирование</h2>
                <div class="action-cards">
                    <div class="action-card">
                        <h3>Интервизии</h3>
                        <p>Управление группами и сессиями</p>
                        <a href="{{ route('admin.intervision.groups') }}" class="btn btn-primary">Управление</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
