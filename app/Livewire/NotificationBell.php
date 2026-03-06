<?php

namespace App\Livewire;

use App\Models\TriadNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public function toggle(): void
    {
        $this->open = !$this->open;
    }

    public function markRead(int $id): void
    {
        TriadNotification::where('id', $id)
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        TriadNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function render()
    {
        $notifications = TriadNotification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $unreadCount = $notifications->whereNull('read_at')->count();

        return view('livewire.notification-bell', compact('notifications', 'unreadCount'));
    }
}
