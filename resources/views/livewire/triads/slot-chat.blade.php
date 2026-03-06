<div class="slot-chat" wire:poll.5s>
    <div class="slot-chat-header">
        Чат слота
    </div>

    <div class="slot-chat-messages" id="chat-messages">
        @forelse($messages as $msg)
            <div class="chat-message {{ $msg->user_id === auth()->id() ? 'chat-message--mine' : '' }}">
                <div class="chat-message-meta">
                    <span class="chat-message-author">{{ $msg->user->name }}</span>
                    <span class="chat-message-time">{{ $msg->created_at->format('H:i') }}</span>
                </div>
                <div class="chat-message-body">{{ $msg->body }}</div>
            </div>
        @empty
            <p class="chat-empty">Сообщений пока нет</p>
        @endforelse
    </div>

    @if($isParticipant)
        <form wire:submit="send" class="slot-chat-form">
            <input type="text"
                   wire:model="body"
                   class="form-control chat-input"
                   placeholder="Написать сообщение..."
                   maxlength="1000"
                   autocomplete="off">
            <button type="submit" class="btn btn-primary btn-sm">→</button>
        </form>
    @else
        <p class="chat-empty" style="padding: 0.75rem; border-top: 1px solid var(--border-color);">
            Чат доступен только участникам слота
        </p>
    @endif
</div>

@push('scripts')
<script>
    // Прокрутка вниз при обновлении чата
    document.addEventListener('livewire:updated', function () {
        const el = document.getElementById('chat-messages');
        if (el) el.scrollTop = el.scrollHeight;
    });
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('chat-messages');
        if (el) el.scrollTop = el.scrollHeight;
    });
</script>
@endpush
