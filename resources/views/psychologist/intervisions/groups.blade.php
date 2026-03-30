@extends('layouts.main')

@section('title', 'Группы интервизий — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Группы интервизий</h1>
        <a href="{{ route('psychologist.intervisions') }}" class="btn btn-outline">Мой статус</a>
    </div>

    <div class="section">
        <h2>Прогресс по обязательным интервизиям</h2>
        <div class="status-card {{ $canConsultInfo['can_consult'] ? 'status-ok' : 'status-warning' }}" style="text-align:left; margin-bottom: 0;">
            <p style="margin-top:0;">
                Посещено за последние 30 дней:
                <strong>{{ $canConsultInfo['attended'] }} / {{ $canConsultInfo['required'] }}</strong>
            </p>

            @if ($canConsultInfo['can_consult'])
                <p style="margin-bottom:0;">Требование выполнено, вы можете консультировать.</p>
            @else
                <p style="margin-bottom:0;">
                    Требуется ещё <strong>{{ $canConsultInfo['remaining'] }}</strong>
                    {{ match(true) {
                        $canConsultInfo['remaining'] === 1 => 'сессия',
                        $canConsultInfo['remaining'] < 5 => 'сессии',
                        default => 'сессий',
                    } }}.
                </p>
            @endif

            <div class="progress-bar" style="margin-top: 0.75rem;">
                <div class="progress-fill"
                     style="width: {{ min(100, ($canConsultInfo['attended'] / max(1, $canConsultInfo['required'])) * 100) }}%"></div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Мои ближайшие сессии</h2>
        @if ($myUpcomingSessions->isEmpty())
            <div class="empty-state">
                <p>У вас пока нет ближайших сессий.</p>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Группа</th>
                        <th>Тема</th>
                        <th>Дата и время</th>
                        <th>Длительность</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($myUpcomingSessions as $session)
                        <tr>
                            <td>{{ $session->group?->name ?? '—' }}</td>
                            <td>{{ $session->topic }}</td>
                            <td>{{ $session->scheduled_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $session->duration_minutes }} мин</td>
                            <td><span class="badge badge-info">{{ $session->status_label }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <h2>Доступные группы</h2>
        @if ($groups->isEmpty())
            <div class="empty-state">
                <p>Сейчас нет активных групп.</p>
            </div>
        @else
            <div class="tasks-grid">
                @foreach ($groups as $group)
                    <div class="task-card card">
                        <div class="task-card-header">
                            <h3 class="task-title">{{ $group->name }}</h3>
                            @if ($group->is_member)
                                <span class="badge badge-success">Вы участник</span>
                            @elseif ($group->has_free_places)
                                <span class="badge badge-info">Есть места</span>
                            @else
                                <span class="badge badge-error">Мест нет</span>
                            @endif
                        </div>

                        @if ($group->description)
                            <p class="task-description">{{ $group->description }}</p>
                        @endif

                        <div class="task-footer">
                            <span>Участников: {{ $group->participants_count }} / {{ $group->max_participants }}</span>
                            <span>Создал: {{ $group->creator?->name ?? '—' }}</span>
                        </div>

                        <div class="form-actions">
                            @if ($group->is_member)
                                <form method="POST" action="{{ route('psychologist.intervisions.groups.leave', $group) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm">Выйти</button>
                                </form>
                            @elseif ($group->has_free_places)
                                <form method="POST" action="{{ route('psychologist.intervisions.groups.join', $group) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Вступить</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-outline btn-sm" disabled>Нет мест</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<style>
.status-card { padding: 1rem 1.25rem; border-radius: 8px; }
.status-ok { background: #d4edda; border: 1px solid #c3e6cb; }
.status-warning { background: #fff3cd; border: 1px solid #ffeeba; }
.progress-bar { height: 10px; background: rgba(0,0,0,0.1); border-radius: 5px; overflow: hidden; }
.progress-fill { height: 100%; background: #28a745; transition: width 0.3s; }
</style>
@endsection
