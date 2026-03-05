@extends('layouts.main')

@section('title', $group->name . ' — Группа интервизий')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>{{ $group->name }}</h1>
        <div class="header-actions">
            <a href="{{ route('admin.intervision.groups.edit', $group->id) }}" class="btn btn-outline">Редактировать</a>
            <a href="{{ route('admin.intervision.groups') }}" class="btn btn-outline">Назад к списку</a>
        </div>
    </div>

    @if ($group->description)
        <div class="description-box">
            <p>{!! nl2br(e($group->description)) !!}</p>
        </div>
    @endif

    <!-- Статистика -->
    @php
        $participantsCount = $group->activeParticipants->count();
        $totalSessions = $sessions->count();
        $completedSessions = $sessions->where('status', 'COMPLETED')->count();
        $upcomingSessions = $sessions->where('status', 'SCHEDULED')->count();
    @endphp
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $participantsCount }}/{{ $group->max_participants }}</div>
            <div class="stat-label">Участников</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $totalSessions }}</div>
            <div class="stat-label">Всего сессий</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $completedSessions }}</div>
            <div class="stat-label">Завершено</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $upcomingSessions }}</div>
            <div class="stat-label">Предстоит</div>
        </div>
    </div>

    <!-- Участники -->
    <div class="section">
        <div class="section-header">
            <h2>Участники группы</h2>
        </div>

        @if ($group->activeParticipants->isNotEmpty())
            <table class="table">
                <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>В группе с</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($group->activeParticipants as $participant)
                    <tr>
                        <td>{{ $participant->psychologist->name }}</td>
                        <td>{{ $participant->psychologist->email }}</td>
                        <td>{{ $participant->joined_at->format('d.m.Y') }}</td>
                        <td>
                            <form action="{{ route('admin.intervision.participants.remove', [$group->id, $participant->psychologist_id]) }}"
                                  method="POST" style="display:inline;"
                                  onsubmit="return confirm('Удалить участника?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty-message">В группе пока нет участников.</p>
        @endif

        @if ($availablePsychologists->isNotEmpty())
            <div class="add-participant-form" style="margin-top: 1.5rem;">
                <h3>Добавить психолога</h3>
                <form action="{{ route('admin.intervision.participants.add', $group->id) }}" method="POST" class="inline-form">
                    @csrf
                    <select name="psychologist_id" required>
                        <option value="">Выберите психолога</option>
                        @foreach ($availablePsychologists as $psy)
                            <option value="{{ $psy->id }}">{{ $psy->name }} ({{ $psy->email }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </form>
            </div>
        @endif
    </div>

    <!-- Сессии -->
    <div class="section">
        <div class="section-header">
            <h2>Сессии интервизий</h2>
            <a href="{{ route('admin.intervision.sessions.create', $group->id) }}" class="btn btn-primary">Запланировать сессию</a>
        </div>

        @if ($sessions->isNotEmpty())
            <table class="table">
                <thead>
                    <tr>
                        <th>Тема</th>
                        <th>Дата и время</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sessions as $session)
                    <tr>
                        <td><strong>{{ $session->topic }}</strong></td>
                        <td>{{ $session->scheduled_at->format('d.m.Y H:i') }}</td>
                        <td>
                            @php
                                $badges = [
                                    'SCHEDULED' => 'badge-info',
                                    'IN_PROGRESS' => 'badge-warning',
                                    'COMPLETED' => 'badge-success',
                                    'CANCELLED' => 'badge-danger',
                                ];
                                $labels = [
                                    'SCHEDULED' => 'Запланирована',
                                    'IN_PROGRESS' => 'В процессе',
                                    'COMPLETED' => 'Завершена',
                                    'CANCELLED' => 'Отменена',
                                ];
                            @endphp
                            <span class="badge {{ $badges[$session->status] ?? '' }}">{{ $labels[$session->status] ?? $session->status }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.intervision.sessions.show', $session->id) }}" class="btn btn-sm">Открыть</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty-message">Сессий пока нет. Запланируйте первую сессию.</p>
        @endif
    </div>

    <!-- Деактивация -->
    <div class="section danger-zone">
        <h2>Опасная зона</h2>
        <form action="{{ route('admin.intervision.groups.delete', $group->id) }}" method="POST"
              onsubmit="return confirm('Деактивировать группу?')">
            @csrf
            <p>Деактивация группы скроет её из списка. Данные сохранятся.</p>
            <button type="submit" class="btn btn-danger">Деактивировать группу</button>
        </form>
    </div>
</div>
@endsection
