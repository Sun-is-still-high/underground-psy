<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiplomaRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $comment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Диплом не прошёл проверку — Underground Psy')
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line('К сожалению, ваш диплом не прошёл проверку.')
            ->line("**Причина:** {$this->comment}")
            ->line('Вы можете загрузить новый скан в личном кабинете.')
            ->action('Загрузить повторно', route('dashboard'))
            ->line('Если у вас есть вопросы — напишите нам на support@underground-psy.ru.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'diploma_rejected',
            'message' => 'Диплом не прошёл проверку: ' . $this->comment,
        ];
    }
}
