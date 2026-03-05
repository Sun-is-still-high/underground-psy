@extends('layouts.main')

@section('title', $event->title . ' — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <a href="{{ route('events.index') }}" class="back-link">&larr; Все мероприятия</a>
        <h1>{{ $event->title }}</h1>
    </div>

    <div class="event-detail">
        <div class="event-detail-main">
            <div class="event-badges">
                <span class="badge badge-outline">{{ $types[$event->event_type] ?? $event->event_type }}</span>
                <span class="badge {{ $event->format === 'ONLINE' ? 'badge-info' : 'badge-secondary' }}">
                    {{ $formats[$event->format] ?? $event->format }}
                </span>
            </div>

            <div class="event-info-grid">
                <div class="event-info-item">
                    <span class="label">Дата и время</span>
                    <span class="value">{{ $event->scheduled_at->format('d.m.Y H:i') }}</span>
                </div>
                <div class="event-info-item">
                    <span class="label">Продолжительность</span>
                    <span class="value">{{ $event->duration_minutes }} мин.</span>
                </div>
                @if ($event->city)
                    <div class="event-info-item">
                        <span class="label">Город</span>
                        <span class="value">{{ $event->city }}</span>
                    </div>
                @endif
                @if ($event->meeting_link)
                    <div class="event-info-item">
                        <span class="label">Ссылка</span>
                        <span class="value">
                            <a href="{{ $event->meeting_link }}" target="_blank" rel="noopener">Присоединиться</a>
                        </span>
                    </div>
                @endif
                <div class="event-info-item">
                    <span class="label">Стоимость</span>
                    <span class="value">
                        @if ($event->price > 0)
                            {{ number_format($event->price, 0, '.', ' ') }} ₽
                        @else
                            Бесплатно
                        @endif
                    </span>
                </div>
                @if ($event->max_participants)
                    <div class="event-info-item">
                        <span class="label">Мест</span>
                        <span class="value">{{ $event->max_participants }}</span>
                    </div>
                @endif
            </div>

            @if ($event->description)
                <div class="event-description-full">
                    <h2>Описание</h2>
                    <p>{!! nl2br(e($event->description)) !!}</p>
                </div>
            @endif
        </div>

        <div class="event-detail-sidebar">
            <div class="organizer-card">
                <h3>Организатор</h3>
                @if ($event->organizer?->psychologistProfile?->photo_url)
                    <img src="{{ $event->organizer->psychologistProfile->photo_url }}"
                         alt="{{ $event->organizer->name }}" class="organizer-photo">
                @endif
                <strong>{{ $event->organizer?->name }}</strong>
                @if ($event->organizer?->psychologistProfile)
                    <a href="{{ route('psychologists.show', $event->organizer->psychologistProfile->id) }}"
                       class="btn btn-outline btn-sm" style="margin-top:.5rem;">Профиль</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
