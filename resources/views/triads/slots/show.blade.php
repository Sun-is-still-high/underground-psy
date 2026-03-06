@extends('layouts.main')

@section('title', '{{ $slot->task->title }} — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>{{ $slot->task->title }}</h1>
            <p class="page-subtitle">
                {{ $slot->starts_at->format('d.m.Y в H:i') }} ·
                {{ $slot->task->duration_minutes }} мин ·
                <span class="badge badge-outline">{{ $slot->status->label() }}</span>
                @if($slot->blind_mode)
                    <span class="badge badge-outline">🙈 Слепой режим</span>
                @endif
            </p>
        </div>
        <a href="{{ route('triads.slots.index') }}" class="btn btn-outline">← К ленте</a>
    </div>

    <div class="slot-show-grid">
        {{-- Основная информация --}}
        <div>
            {{-- Описание задания --}}
            <div class="card" style="padding: 1.25rem; margin-bottom: 1rem;">
                <h3 style="margin-bottom: 0.5rem;">О задании</h3>
                <p>{{ $slot->task->description }}</p>
            </div>

            {{-- Участники и роли --}}
            <div class="card" style="padding: 1.25rem; margin-bottom: 1rem;">
                <h3 style="margin-bottom: 1rem;">Участники</h3>
                @php
                    $roleLabels = ['therapist' => 'Терапевт', 'client' => 'Клиент', 'observer' => 'Наблюдатель'];
                    $roleColors = ['therapist' => 'role-badge--therapist', 'client' => 'role-badge--client', 'observer' => 'role-badge--observer'];
                    $takenRoles = $slot->activeParticipants->keyBy(fn($p) => $p->role->value ?? $p->role);
                @endphp

                <div class="participants-list">
                    @foreach(['therapist', 'client', 'observer'] as $role)
                        @php $participant = $takenRoles->get($role); @endphp
                        <div class="participant-row">
                            <span class="role-badge {{ $roleColors[$role] }}">{{ $roleLabels[$role] }}</span>
                            @if($participant)
                                <span class="participant-name">{{ $participant->user->name }}</span>
                                @if($participant->user_id === auth()->id())
                                    <span class="badge badge-info">Вы</span>
                                @endif
                            @else
                                <span class="text-light">— свободно —</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Кнопка входа в сессию (за 5 минут до начала) --}}
            @if($myParticipation && $slot->isConnectable())
                <div class="card" style="padding: 1.25rem; margin-bottom: 1rem; border-color: var(--primary-color);">
                    <a href="{{ route('triads.slots.session', $slot) }}" class="btn btn-primary" style="width: 100%; text-align: center; font-size: 1.1rem;">
                        🎥 Войти в сессию
                    </a>
                </div>
            @elseif($myParticipation && in_array($slot->status->value, ['full', 'in_progress']))
                <div class="alert" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; border-radius: 0.5rem; padding: 0.75rem 1rem; margin-bottom: 1rem;">
                    Вход откроется за 5 минут до начала
                    (в {{ $slot->starts_at->subMinutes(5)->format('H:i') }})
                </div>
            @endif

            {{-- Кнопки действий --}}
            @if($slot->status === \App\Enums\SlotStatus::Open || $slot->status === \App\Enums\SlotStatus::Full)
                @if($myParticipation)
                    {{-- Участник — кнопка отписки --}}
                    <form method="POST" action="{{ route('triads.slots.leave', $slot) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline"
                                onclick="return confirm('Отписаться от слота?')">
                            Отписаться
                        </button>
                    </form>
                @elseif($slot->isJoinable())
                    {{-- Не участник — кнопки записи на свободные роли --}}
                    @php $availableRoles = $slot->availableRoles(); @endphp
                    @if(count($availableRoles) > 0 && auth()->user()->isPsychologist())
                        <div class="card" style="padding: 1.25rem;">
                            <h3 style="margin-bottom: 1rem;">Записаться</h3>
                            <form method="POST" action="{{ route('triads.slots.join', $slot) }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                                @csrf
                                <select name="role" class="form-control" style="max-width: 200px;" required>
                                    <option value="">Выберите роль</option>
                                    @foreach($availableRoles as $role)
                                        <option value="{{ $role }}">{{ $roleLabels[$role] }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary">Записаться</button>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="alert alert-error">Запись закрыта (менее 1 часа до начала)</div>
                @endif
            @endif

            {{-- Входящее приглашение --}}
            @if($myInvitation)
                @php $roleLabelsInv = ['therapist' => 'Терапевт', 'client' => 'Клиент', 'observer' => 'Наблюдатель']; @endphp
                <div class="card invitation-incoming" style="padding: 1.25rem; margin-top: 1rem; border-color: var(--primary-color);">
                    <h3 style="margin-bottom: 0.5rem;">Вас приглашают в тройку</h3>
                    <p style="margin-bottom: 1rem; color: var(--text-secondary);">
                        Роль: <strong>{{ $roleLabelsInv[$myInvitation->proposed_role->value] }}</strong>
                    </p>
                    <div style="display: flex; gap: 0.75rem;">
                        <form method="POST" action="{{ route('triads.slots.invitations.accept', [$slot, $myInvitation]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">Принять</button>
                        </form>
                        <form method="POST" action="{{ route('triads.slots.invitations.decline', [$slot, $myInvitation]) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline">Отклонить</button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Форма приглашения (только создатель + есть свободные роли) --}}
            @if($slot->creator_id === auth()->id() && in_array($slot->status->value, ['open', 'full']) && count($slot->availableRoles()) > 0)
                <div class="card" style="padding: 1.25rem; margin-top: 1rem;">
                    <h3 style="margin-bottom: 1rem;">Пригласить психолога</h3>
                    @if($availablePsychologists->isEmpty())
                        <p class="text-light" style="font-size: 0.875rem;">Все психологи уже приглашены или участвуют</p>
                    @else
                        <form method="POST" action="{{ route('triads.slots.invitations.store', $slot) }}"
                              style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
                            @csrf
                            <div style="flex: 1; min-width: 160px;">
                                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem; display: block;">Психолог</label>
                                <select name="invitee_id" class="form-control" required>
                                    <option value="">Выберите психолога</option>
                                    @foreach($availablePsychologists as $psych)
                                        <option value="{{ $psych->id }}">{{ $psych->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="min-width: 140px;">
                                <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem; display: block;">Роль</label>
                                <select name="proposed_role" class="form-control" required>
                                    <option value="">Выберите роль</option>
                                    @foreach($slot->availableRoles() as $role)
                                        <option value="{{ $role }}">{{ $roleLabels[$role] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Пригласить</button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Кнопка отмены слота (только автор) --}}
            @if($slot->creator_id === auth()->id() && in_array($slot->status->value, ['open', 'full']))
                <form method="POST" action="{{ route('triads.slots.cancel', $slot) }}"
                      style="margin-top: 1rem;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Отменить слот? Все участники получат уведомление.')">
                        Отменить слот
                    </button>
                </form>
            @endif
        </div>

        {{-- Чат слота --}}
        @auth
            @livewire('triads.slot-chat', ['slot' => $slot])
        @endauth

        {{-- Боковая панель --}}
        <div>
            <div class="card slot-info-card">
                <div class="slot-info-row">
                    <span class="slot-info-label">Автор</span>
                    <span>{{ $slot->creator->name }}</span>
                </div>
                <div class="slot-info-row">
                    <span class="slot-info-label">Дата</span>
                    <span>{{ $slot->starts_at->format('d.m.Y') }}</span>
                </div>
                <div class="slot-info-row">
                    <span class="slot-info-label">Время</span>
                    <span>{{ $slot->starts_at->format('H:i') }} — {{ $slot->endsAt()->format('H:i') }}</span>
                </div>
                <div class="slot-info-row">
                    <span class="slot-info-label">Длительность</span>
                    <span>{{ $slot->task->duration_minutes }} мин</span>
                </div>
                <div class="slot-info-row">
                    <span class="slot-info-label">Видимость</span>
                    <span>{{ $slot->visibility === 'public' ? 'Публичный' : 'Приватный' }}</span>
                </div>
                <div class="slot-info-row">
                    <span class="slot-info-label">Статус</span>
                    <span class="badge {{ match($slot->status->value) {
                        'open' => 'badge-info',
                        'full' => 'badge-warning',
                        'in_progress' => 'badge-success',
                        'completed' => 'badge-secondary',
                        'cancelled' => 'badge-error',
                        default => 'badge-outline'
                    } }}">{{ $slot->status->label() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
