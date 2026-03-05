@extends('layouts.main')

@section('title', 'Мои тройки — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Мои тройки</h1>
        @if(auth()->user()->isPsychologist())
            <a href="{{ route('triads.slots.create') }}" class="btn btn-primary">+ Создать слот</a>
        @endif
    </div>

    @php
        $roleLabels = ['therapist' => 'Терапевт', 'client' => 'Клиент', 'observer' => 'Наблюдатель'];
        $roleColors = ['therapist' => 'role-badge--therapist', 'client' => 'role-badge--client', 'observer' => 'role-badge--observer'];

        $statusBadge = fn($s) => match($s) {
            'open'        => 'badge-info',
            'full'        => 'badge-warning',
            'in_progress' => 'badge-success',
            'completed'   => 'badge-secondary',
            'cancelled'   => 'badge-error',
            default       => 'badge-outline',
        };
    @endphp

    {{-- Слоты, на которые записан --}}
    <h2 style="margin-bottom: 1rem;">Записан</h2>

    @if($participating->isEmpty())
        <div class="empty-state" style="margin-bottom: 2rem;">
            <p>Вы не записаны ни на один слот.</p>
            <a href="{{ route('triads.slots.index') }}" class="btn btn-primary" style="margin-top: 1rem;">Найти слот</a>
        </div>
    @else
        <div class="tasks-list" style="margin-bottom: 2rem;">
            @foreach($participating as $slot)
                @php
                    $myRole = $slot->activeParticipants->firstWhere('user_id', auth()->id());
                @endphp
                <div class="task-list-item card">
                    <div class="task-list-header">
                        <h3 class="task-title">
                            <a href="{{ route('triads.slots.show', $slot) }}">{{ $slot->task->title }}</a>
                        </h3>
                        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                            @if($myRole)
                                <span class="role-badge {{ $roleColors[$myRole->role->value ?? $myRole->role] ?? '' }}">
                                    Я: {{ $roleLabels[$myRole->role->value ?? $myRole->role] ?? '?' }}
                                </span>
                            @endif
                            <span class="badge {{ $statusBadge($slot->status->value) }}">{{ $slot->status->label() }}</span>
                        </div>
                    </div>
                    <p class="text-light">
                        {{ $slot->starts_at->format('d.m.Y в H:i') }} · {{ $slot->task->duration_minutes }} мин
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Созданные слоты --}}
    @if(auth()->user()->isPsychologist())
        <h2 style="margin-bottom: 1rem;">Созданные мной</h2>

        @if($created->isEmpty())
            <div class="empty-state">
                <p>Вы ещё не создавали слотов.</p>
            </div>
        @else
            <div class="tasks-list">
                @foreach($created as $slot)
                    <div class="task-list-item card">
                        <div class="task-list-header">
                            <h3 class="task-title">
                                <a href="{{ route('triads.slots.show', $slot) }}">{{ $slot->task->title }}</a>
                            </h3>
                            <span class="badge {{ $statusBadge($slot->status->value) }}">{{ $slot->status->label() }}</span>
                        </div>
                        <p class="text-light">
                            {{ $slot->starts_at->format('d.m.Y в H:i') }} ·
                            {{ $slot->task->duration_minutes }} мин ·
                            {{ $slot->activeParticipants->count() }}/3 участников
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
