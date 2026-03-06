@extends('layouts.main')

@section('title', 'Сессия: {{ $slot->task->title }} — Underground Psy')

@section('content')
<div class="session-layout">

    {{-- ===== ЛЕВАЯ ПАНЕЛЬ: инструкции + управление ===== --}}
    <aside class="session-sidebar">

        {{-- Заголовок --}}
        <div class="session-sidebar-header">
            <h2 class="session-task-title">{{ $slot->task->title }}</h2>
            <div class="session-meta">
                <span class="role-badge role-badge--{{ $myRole->value }}">
                    Вы: {{ $myRole->label() }}
                </span>
                @if($slot->blind_mode)
                    <span class="badge badge-outline" style="font-size: 0.7rem;">🙈 Слепой режим</span>
                @endif
            </div>
        </div>

        {{-- Таймер --}}
        <div class="session-timer-block">
            <div class="session-timer-label">
                @if($isExpired)
                    Время сессии истекло
                @else
                    Сессия заканчивается в
                @endif
            </div>
            <div class="session-timer" id="session-timer"
                 data-ends="{{ $endsAt->toIso8601String() }}">
                {{ $endsAt->format('H:i') }}
            </div>
            <div class="session-timer-sub text-light">
                {{ $slot->starts_at->format('d.m.Y') }} ·
                {{ $slot->task->duration_minutes }} мин
            </div>
        </div>

        {{-- Описание задания --}}
        <details class="session-details">
            <summary>Описание кейса</summary>
            <p class="session-details-body">{{ $slot->task->description }}</p>
        </details>

        {{-- Инструкции по ролям --}}
        <div class="session-instructions">
            @php
                $roleLabels = ['therapist' => 'Терапевт', 'client' => 'Клиент', 'observer' => 'Наблюдатель'];
                $roleColors = ['therapist' => 'role-badge--therapist', 'client' => 'role-badge--client', 'observer' => 'role-badge--observer'];
            @endphp

            @foreach(['therapist', 'client', 'observer'] as $role)
                @php $text = $instructions[$role] ?? null; @endphp
                <div class="instruction-block {{ $myRole->value === $role ? 'instruction-block--mine' : '' }}">
                    <div class="instruction-label">
                        <span class="role-badge {{ $roleColors[$role] }}">{{ $roleLabels[$role] }}</span>
                        @if($myRole->value === $role)
                            <span class="text-light" style="font-size: 0.75rem;">← Ваша роль</span>
                        @endif
                    </div>
                    @if($text)
                        <p class="instruction-text">{{ $text }}</p>
                    @else
                        <p class="instruction-text instruction-text--hidden">
                            🙈 Скрыто в слепом режиме
                        </p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Участники --}}
        <div class="session-participants">
            <div class="session-section-title">Участники</div>
            @foreach($slot->activeParticipants as $p)
                <div class="participant-row">
                    <span class="role-badge {{ $roleColors[$p->role->value] }}">{{ $roleLabels[$p->role->value] }}</span>
                    <span class="participant-name">{{ $p->user->name }}</span>
                    @if($p->confirmed_completion)
                        <span class="badge badge-success" style="font-size: 0.7rem;">✓</span>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Перераспределение ролей --}}
        @if($isExpired === false && count($availableForReassign) > 0)
            <details class="session-details">
                <summary>Перераспределить роль</summary>
                <div class="session-details-body">
                    <p class="text-light" style="font-size: 0.85rem; margin-bottom: 0.75rem;">
                        Используйте, если кто-то не пришёл. Терапевт и клиент обязательны.
                    </p>
                    <form method="POST" action="{{ route('triads.slots.reassign', $slot) }}">
                        @csrf
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <select name="new_role" class="form-control" style="flex: 1;">
                                @foreach($availableForReassign as $role)
                                    <option value="{{ $role }}">{{ $roleLabels[$role] }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline btn-sm">Взять</button>
                        </div>
                    </form>
                </div>
            </details>
        @endif

        {{-- Подтверждение завершения --}}
        @if($isExpired)
            <div class="session-confirm-block">
                <div class="session-section-title">Подтверждение сессии</div>
                @if($myParticipation->confirmed_completion)
                    <div class="alert alert-success">Вы подтвердили завершение сессии</div>
                @else
                    <p class="text-light" style="font-size: 0.85rem; margin-bottom: 0.75rem;">
                        Подтвердите, что сессия состоялась. Зачёт будет выдан когда все участники подтвердят.
                    </p>
                    <form method="POST" action="{{ route('triads.slots.confirm', $slot) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Подтвердить завершение сессии
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="session-confirm-block session-confirm-block--waiting">
                <p class="text-light" style="font-size: 0.85rem;">
                    Кнопка подтверждения появится после окончания сессии
                </p>
            </div>
        @endif

        {{-- Чат слота --}}
        <div style="margin-top: 1rem;">
            @livewire('triads.slot-chat', ['slot' => $slot])
        </div>

        {{-- Ссылка назад --}}
        <a href="{{ route('triads.slots.show', $slot) }}" class="btn btn-outline btn-sm" style="margin-top: 0.5rem;">
            ← К слоту
        </a>
    </aside>

    {{-- ===== ПРАВАЯ ЧАСТЬ: Jitsi видеозвонок ===== --}}
    <main class="session-video">
        <div class="jitsi-container" id="jitsi-container">
            <div class="jitsi-loading" id="jitsi-loading">
                <p>Подключение к видеозвонку...</p>
            </div>
        </div>
    </main>

</div>

@push('scripts')
<script>
    // ======= Таймер обратного отсчёта =======
    (function () {
        const el      = document.getElementById('session-timer');
        const endsAt  = new Date(el.dataset.ends);

        function tick() {
            const diff = Math.floor((endsAt - Date.now()) / 1000);
            if (diff <= 0) {
                el.textContent = '00:00';
                el.classList.add('session-timer--expired');
                // Перезагружаем страницу чтобы показать кнопку подтверждения
                if (!el.dataset.reloaded) {
                    el.dataset.reloaded = '1';
                    setTimeout(() => location.reload(), 2000);
                }
                return;
            }
            const h = Math.floor(diff / 3600);
            const m = Math.floor((diff % 3600) / 60);
            const s = diff % 60;
            el.textContent = (h > 0 ? String(h).padStart(2,'0') + ':' : '')
                           + String(m).padStart(2,'0') + ':'
                           + String(s).padStart(2,'0');

            if (diff <= 300) el.classList.add('session-timer--soon'); // последние 5 минут
        }

        tick();
        setInterval(tick, 1000);
    })();

    // ======= Jitsi Meet =======
    (function () {
        const roomName  = @json($jitsiRoom);
        const userName  = @json(auth()->user()->name);
        const container = document.getElementById('jitsi-container');
        const loading   = document.getElementById('jitsi-loading');

        const domain = 'meet.jit.si';
        const options = {
            roomName:   roomName,
            parentNode: container,
            userInfo: {
                displayName: userName,
            },
            configOverwrite: {
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                disableDeepLinking:  true,
                defaultLanguage:     'ru',
                enableWelcomePage:   false,
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop',
                    'fullscreen', 'fodeviceselection', 'hangup', 'chat',
                    'settings', 'raisehand', 'videoquality', 'filmstrip',
                    'tileview',
                ],
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
            },
        };

        // Загружаем Jitsi Meet External API
        const script = document.createElement('script');
        script.src   = 'https://meet.jit.si/external_api.js';
        script.onload = function () {
            loading.style.display = 'none';
            const api = new JitsiMeetExternalAPI(domain, options);

            api.addEventListeners({
                videoConferenceJoined: function () {
                    container.style.background = '#1a1a2e';
                },
            });
        };
        script.onerror = function () {
            loading.innerHTML = '<p style="color:#ef4444">Не удалось загрузить Jitsi Meet.<br>Проверьте соединение с интернетом.</p>';
        };
        document.head.appendChild(script);
    })();
</script>
@endpush
@endsection
