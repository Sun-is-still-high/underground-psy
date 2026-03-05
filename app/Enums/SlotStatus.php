<?php

namespace App\Enums;

enum SlotStatus: string
{
    case Open        = 'open';
    case Full        = 'full';
    case InProgress  = 'in_progress';
    case Completed   = 'completed';
    case Cancelled   = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Open       => 'Открыт',
            self::Full       => 'Набран',
            self::InProgress => 'Идёт сессия',
            self::Completed  => 'Завершён',
            self::Cancelled  => 'Отменён',
        };
    }
}
