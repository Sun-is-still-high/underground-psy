<?php

namespace App\Notifications;

use App\Models\Slot;
use App\Models\SlotInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Slot $slot,
        public readonly SlotInvitation $invitation
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->slot->scheduled_at->format('d.m.Y H:i');
        $role = match ($this->invitation->role) {
            'therapist' => 'терапевта',
            'client'    => 'клиента',
            'observer'  => 'наблюдателя',
            default     => $this->invitation->role,
        };

        return (new MailMessage)
            ->subject("Приглашение в тройку — Underground Psy")
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line("Вас пригласили в тройку на {$date} в роли {$role}.")
            ->action('Посмотреть приглашение', route('triads.slots.show', $this->slot->id))
            ->line('Примите или отклоните приглашение в личном кабинете.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'invitation_received',
            'slot_id'       => $this->slot->id,
            'invitation_id' => $this->invitation->id,
            'message'       => 'Вас пригласили в тройку на ' . $this->slot->scheduled_at->format('d.m.Y H:i') . '.',
        ];
    }
}
