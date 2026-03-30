<?php

namespace App\Enums;

enum IntervisionSessionStatus: string
{
    case Scheduled = 'SCHEDULED';
    case InProgress = 'IN_PROGRESS';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Запланирована',
            self::InProgress => 'Идёт',
            self::Completed => 'Завершена',
            self::Cancelled => 'Отменена',
        };
    }

    public static function labelOf(string $value): string
    {
        return self::tryFrom($value)?->label() ?? $value;
    }
}
