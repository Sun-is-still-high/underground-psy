<div class="notification-bell" wire:poll.15s>
    {{-- Кнопка-колокольчик --}}
    <button class="bell-btn" wire:click="toggle" type="button">
        <span class="bell-icon">🔔</span>
        @if($unreadCount > 0)
            <span class="bell-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    {{-- Выпадающий список --}}
    @if($open)
        <div class="bell-dropdown">
            <div class="bell-dropdown-header">
                <span>Уведомления</span>
                @if($unreadCount > 0)
                    <button wire:click="markAllRead" class="bell-mark-all" type="button">
                        Прочитать все
                    </button>
                @endif
            </div>

            @forelse($notifications as $n)
                @php
                    $labels = [
                        'slot_cancelled'      => 'Слот отменён',
                        'participant_left'     => 'Участник отписался',
                        'participant_joined'   => 'Новый участник',
                        'invitation_received'  => 'Приглашение в тройку',
                        'invitation_accepted'  => 'Приглашение принято',
                        'invitation_declined'  => 'Приглашение отклонено',
                        'session_starting_soon'=> 'Сессия начинается',
                        'task_approved'        => 'Задание одобрено',
                        'task_rejected'        => 'Задание отклонено',
                    ];
                    $label = $labels[$n->type] ?? $n->type;
                    $slotId = $n->data['slot_id'] ?? null;
                @endphp
                <div class="bell-item {{ $n->read_at ? '' : 'bell-item--unread' }}"
                     wire:click="markRead({{ $n->id }})">
                    <div class="bell-item-title">{{ $label }}</div>
                    @if(isset($n->data['task_title']))
                        <div class="bell-item-sub">{{ $n->data['task_title'] }}</div>
                    @elseif(isset($n->data['user_name']))
                        <div class="bell-item-sub">{{ $n->data['user_name'] }}</div>
                    @elseif(isset($n->data['comment']))
                        <div class="bell-item-sub">{{ Str::limit($n->data['comment'], 60) }}</div>
                    @endif
                    <div class="bell-item-time">{{ $n->created_at->diffForHumans() }}</div>
                    @if($slotId)
                        <a href="{{ route('triads.slots.show', $slotId) }}"
                           class="bell-item-link"
                           wire:click.stop>Перейти →</a>
                    @endif
                </div>
            @empty
                <div class="bell-empty">Уведомлений нет</div>
            @endforelse
        </div>

        {{-- Клик вне — закрыть --}}
        <div class="bell-overlay" wire:click="toggle"></div>
    @endif
</div>
