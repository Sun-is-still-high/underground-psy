<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiplomaApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ваш диплом подтверждён — Underground Psy')
            ->greeting("Здравствуйте, {$notifiable->name}!")
            ->line('Ваш диплом успешно прошёл проверку. Теперь у вас есть полный доступ к платформе.')
            ->action('Перейти в личный кабинет', route('dashboard'))
            ->line('Заполните профиль и опубликуйте его, чтобы клиенты могли вас найти.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'diploma_approved',
            'message' => 'Ваш диплом подтверждён. Добро пожаловать на платформу!',
        ];
    }
}
