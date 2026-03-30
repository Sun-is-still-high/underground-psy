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

        @if ($user->isPsychologist() && $user->isPendingVerification())
            @php $profile = $user->psychologistProfile; @endphp
            <div class="alert alert-warning">
                <strong>Аккаунт ожидает проверки диплома.</strong>
                Срок проверки - до 2 недель. Пока доступ к платформе ограничен.<br>
                <small>Если проверка затягивается - напишите на <a href="mailto:support@underground-psy.ru">support@underground-psy.ru</a>.</small>

                @if ($profile && $profile->diploma_rejection_comment)
                    <hr>
                    <strong>Ваш диплом был отклонён:</strong> {{ $profile->diploma_rejection_comment }}<br>
                    <small>Загрузите новый скан через форму ниже.</small>
                @endif
            </div>

            @if ($profile && $profile->diploma_rejection_comment)
                <div class="card" style="margin-top: 1rem; padding: 1.5rem;">
                    <h3>Загрузить новый скан диплома</h3>
                    <form action="{{ route('psychologist.diploma.reupload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                            </div>
                        @endif
                        <div class="form-group">
                            <label>Скан диплома (JPG, PNG или PDF, не более 10 МБ)</label>
                            <input type="file" name="diploma_scan" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <div class="form-group">
                            <label>Номер диплома</label>
                            <input type="text" name="diploma_number" class="form-control" value="{{ old('diploma_number', $profile->diploma_number) }}" maxlength="100" required>
                        </div>
                        <div class="form-group">
                            <label>Год выдачи</label>
                            <input type="number" name="diploma_year" class="form-control" value="{{ old('diploma_year', $profile->diploma_year) }}" min="1950" max="{{ date('Y') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Учебное заведение</label>
                            <input type="text" name="diploma_institution" class="form-control" value="{{ old('diploma_institution', $profile->diploma_institution) }}" maxlength="255" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Отправить на повторную проверку</button>
                    </form>
                </div>
            @endif
        @endif

        @if ($profileWarning && !($user->isPsychologist() && $user->isPendingVerification()))
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
                        <p>Опишите проблему - психологи откликнутся</p>
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
            @if ($canConsultInfo)
                @if (!$canConsultInfo['can_consult'])
                    <div class="alert alert-warning">
                        Вы посетили <strong>{{ $canConsultInfo['attended'] }}</strong>
                        из <strong>{{ $canConsultInfo['required'] }}</strong> обязательных интервизий за последние 30 дней.
                        Для допуска к консультациям посетите ещё
                        <strong>{{ max(0, $canConsultInfo['required'] - $canConsultInfo['attended']) }}</strong>.
                    </div>
                @else
                    <div class="alert alert-success">
                        Вы допущены к консультациям ({{ $canConsultInfo['attended'] }} интервизий за 30 дней).
                    </div>
                @endif
            @endif

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
                        <a href="{{ route('psychologist.intervisions.groups') }}" class="btn btn-outline">Перейти</a>
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