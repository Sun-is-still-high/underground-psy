@extends('layouts.main')

@section('title', 'Мероприятия — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Мероприятия</h1>
        <p class="page-subtitle">Групповая терапия, семинары, тренинги от психологов платформы</p>
    </div>

    {{-- Фильтры --}}
    <form method="GET" action="{{ route('events.index') }}" class="filters-form">
        <div class="filters-row">
            <select name="event_type" class="form-control" onchange="this.form.submit()">
                <option value="">Все типы</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" {{ ($filters['event_type'] ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <select name="format" class="form-control" onchange="this.form.submit()">
                <option value="">Все форматы</option>
                @foreach ($formats as $value => $label)
                    <option value="{{ $value }}" {{ ($filters['format'] ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            @if (!empty(array_filter($filters)))
                <a href="{{ route('events.index') }}" class="btn btn-outline btn-sm">Сбросить</a>
            @endif
        </div>
    </form>

    @if ($events->isEmpty())
        <div class="empty-state">
            <p>Нет предстоящих мероприятий по выбранным фильтрам.</p>
        </div>
    @else
        <div class="events-grid">
            @foreach ($events as $event)
                <div class="event-card">
                    <div class="event-card-header">
                        <span class="badge badge-outline">{{ $types[$event->event_type] ?? $event->event_type }}</span>
                        <span class="badge {{ $event->format === 'ONLINE' ? 'badge-info' : 'badge-secondary' }}">
                            {{ $formats[$event->format] ?? $event->format }}
                        </span>
                    </div>

                    <h3 class="event-title">
                        <a href="{{ route('events.show', $event) }}">{{ $event->title }}</a>
                    </h3>

                    <div class="event-meta">
                        <span class="event-date">{{ $event->scheduled_at->format('d.m.Y H:i') }}</span>
                        @if ($event->city)
                            <span class="event-city">{{ $event->city }}</span>
                        @endif
                        @if ($event->price > 0)
                            <span class="event-price">{{ number_format($event->price, 0, '.', ' ') }} ₽</span>
                        @else
                            <span class="event-price free">Бесплатно</span>
                        @endif
                    </div>

                    @if ($event->description)
                        <p class="event-description">{{ Str::limit($event->description, 120) }}</p>
                    @endif

                    <div class="event-organizer">
                        @if ($event->organizer?->psychologistProfile?->photo_url)
                            <img src="{{ $event->organizer->psychologistProfile->photo_url }}"
                                 alt="{{ $event->organizer->name }}" class="avatar-sm">
                        @endif
                        <span>{{ $event->organizer?->name }}</span>
                    </div>

                    <a href="{{ route('events.show', $event) }}" class="btn btn-primary btn-sm">Подробнее</a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
