<?php

namespace App\Notifications;

use App\Models\Slot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlotCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Slot $slot) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->slot->scheduled_at->format('d.m.Y H:i');

        return (new MailMessage)
            ->subject("Слот тройки отменён — Underground Psy")
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line("Слот тройки на {$date} был отменён организатором.")
            ->action('Найти другой слот', route('triads.slots.index'))
            ->line('Вы можете записаться на другое время.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'slot_cancelled',
            'slot_id' => $this->slot->id,
            'message' => 'Слот тройки на ' . $this->slot->scheduled_at->format('d.m.Y H:i') . ' отменён.',
        ];
    }
}
