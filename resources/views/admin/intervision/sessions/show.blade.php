@extends('layouts.main')

@section('title', $session->topic . ' — Сессия интервизии')

@section('content')
@php
    $statusBadges = [
        'SCHEDULED' => 'badge-info',
        'IN_PROGRESS' => 'badge-warning',
        'COMPLETED' => 'badge-success',
        'CANCELLED' => 'badge-danger',
    ];
    $statusLabels = [
        'SCHEDULED' => 'Запланирована',
        'IN_PROGRESS' => 'В процессе',
        'COMPLETED' => 'Завершена',
        'CANCELLED' => 'Отменена',
    ];
@endphp
<div class="container">
    <div class="page-header">
        <h1>{{ $session->topic }}</h1>
        <div class="header-actions">
            <a href="{{ route('admin.intervision.groups.show', $session->group_id) }}" class="btn btn-outline">Назад к группе</a>
        </div>
    </div>

    <div class="session-meta">
        <p><strong>Группа:</strong> {{ $session->group->name }}</p>
        <p><strong>Статус:</strong> <span class="badge {{ $statusBadges[$session->status] ?? '' }}">{{ $statusLabels[$session->status] ?? $session->status }}</span></p>
        <p><strong>Дата и время:</strong> {{ $session->scheduled_at->format('d.m.Y H:i') }}</p>
        <p><strong>Длительность:</strong> {{ $session->duration_minutes }} мин</p>
        @if ($session->meeting_link)
            <p><strong>Ссылка:</strong> <a href="{{ $session->meeting_link }}" target="_blank">{{ $session->meeting_link }}</a></p>
        @endif
    </div>

    @if ($session->description)
        <div class="description-box">
            <h3>Описание</h3>
            <p>{!! nl2br(e($session->description)) !!}</p>
        </div>
    @endif

    @if ($session->status === 'CANCELLED' && $session->cancelled_reason)
        <div class="alert alert-warning">
            <strong>Причина отмены:</strong> {{ $session->cancelled_reason }}
        </div>
    @endif

    <!-- Посещаемость -->
    <div class="section">
        <div class="section-header">
            <h2>Посещаемость</h2>
            @if ($session->status !== 'CANCELLED')
                <a href="{{ route('admin.intervision.sessions.attendance', $session->id) }}" class="btn btn-primary">Отметить посещаемость</a>
            @endif
        </div>

        @if ($session->attendance->isNotEmpty())
            <table class="table">
                <thead>
                    <tr>
                        <th>Участник</th>
                        <th>Email</th>
                        <th>Присутствовал</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($session->attendance as $record)
                    <tr>
                        <td>{{ $record->participant->psychologist->name }}</td>
                        <td>{{ $record->participant->psychologist->email }}</td>
                        <td>
                            @if ($record->attended)
                                <span class="badge badge-success">Да</span>
                            @else
                                <span class="badge badge-secondary">Нет</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="empty-message">Данные о посещаемости ещё не внесены.</p>
        @endif
    </div>

    <!-- Управление статусом -->
    @if ($session->status === 'SCHEDULED')
    <div class="section">
        <h2>Управление сессией</h2>
        <div class="button-group">
            <form action="{{ route('admin.intervision.sessions.status', $session->id) }}" method="POST" style="display:inline;"
                  onsubmit="return confirm('Отменить сессию?')">
                @csrf
                <input type="hidden" name="status" value="CANCELLED">
                <input type="text" name="cancelled_reason" placeholder="Причина отмены" style="width:200px;">
                <button type="submit" class="btn btn-danger">Отменить сессию</button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
