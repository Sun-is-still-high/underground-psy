<?php

namespace App\Enums;

enum ParticipantRole: string
{
    case Therapist = 'therapist';
    case Client    = 'client';
    case Observer  = 'observer';

    public function label(): string
    {
        return match($this) {
            self::Therapist => 'Терапевт',
            self::Client    => 'Клиент',
            self::Observer  => 'Наблюдатель',
        };
    }
}
