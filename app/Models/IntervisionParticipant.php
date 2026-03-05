<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntervisionParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = ['group_id', 'psychologist_id', 'is_active', 'left_at'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'left_at' => 'datetime',
            'joined_at' => 'datetime',
        ];
    }

    public function group()
    {
        return $this->belongsTo(IntervisionGroup::class, 'group_id');
    }

    public function psychologist()
    {
        return $this->belongsTo(User::class, 'psychologist_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(IntervisionAttendance::class, 'participant_id');
    }
}
