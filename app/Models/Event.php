<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'organizer_id',
        'title',
        'description',
        'event_type',
        'format',
        'city',
        'meeting_link',
        'price',
        'max_participants',
        'scheduled_at',
        'duration_minutes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'price'        => 'decimal:2',
        ];
    }

    public const TYPES = [
        'GROUP_THERAPY' => 'Групповая терапия',
        'SUPPORT_GROUP' => 'Группа поддержки',
        'SEMINAR'       => 'Семинар',
        'TRAINING'      => 'Тренинг',
        'WEBINAR'       => 'Вебинар',
    ];

    public const FORMATS = [
        'ONLINE'  => 'Онлайн',
        'OFFLINE' => 'Офлайн',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now());
    }
}
