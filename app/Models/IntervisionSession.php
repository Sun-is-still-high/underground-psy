<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntervisionSession extends Model
{
    protected $fillable = [
        'group_id',
        'topic',
        'description',
        'scheduled_at',
        'duration_minutes',
        'meeting_link',
        'status',
        'cancelled_reason',
    ];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime'];
    }

    public function group()
    {
        return $this->belongsTo(IntervisionGroup::class, 'group_id');
    }

    public function attendance()
    {
        return $this->hasMany(IntervisionAttendance::class, 'session_id');
    }
}
