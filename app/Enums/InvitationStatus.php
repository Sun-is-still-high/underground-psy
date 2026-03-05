<?php

namespace App\Enums;

enum InvitationStatus: string
{
    case Pending  = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';

    public function label(): string
    {
        return match($this) {
            self::Pending  => 'Ожидает ответа',
            self::Accepted => 'Принято',
            self::Declined => 'Отклонено',
        };
    }
}
