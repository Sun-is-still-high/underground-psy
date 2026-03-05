@extends('layouts.main')

@section('title', 'Мои мероприятия — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Мои мероприятия</h1>
        <a href="{{ route('psychologist.events.create') }}" class="btn btn-primary">+ Создать</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($events->isEmpty())
        <div class="empty-state">
            <p>У вас пока нет мероприятий.</p>
            <a href="{{ route('psychologist.events.create') }}" class="btn btn-primary" style="margin-top:1rem;">Создать первое</a>
        </div>
    @else
        @php
            $statusLabels = ['ACTIVE' => 'Активно', 'CANCELLED' => 'Отменено', 'COMPLETED' => 'Завершено'];
        @endphp
        <div class="events-table">
            @foreach ($events as $event)
                <div class="event-row">
                    <div class="event-row-info">
                        <strong>{{ $event->title }}</strong>
                        <span class="text-muted">{{ $types[$event->event_type] ?? $event->event_type }}</span>
                    </div>
                    <div class="event-row-date">
                        {{ $event->scheduled_at->format('d.m.Y H:i') }}
                    </div>
                    <div class="event-row-status">
                        <span class="badge {{ $event->status === 'ACTIVE' ? 'badge-success' : 'badge-outline' }}">
                            {{ $statusLabels[$event->status] ?? $event->status }}
                        </span>
                    </div>
                    <div class="event-row-actions">
                        <a href="{{ route('events.show', $event) }}" class="btn btn-outline btn-sm">Посмотреть</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
