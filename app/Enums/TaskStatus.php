<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Draft    = 'draft';
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Draft    => 'Черновик',
            self::Pending  => 'На модерации',
            self::Approved => 'Одобрено',
            self::Rejected => 'Отклонено',
        };
    }
}
