<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntervisionAttendance extends Model
{
    public $timestamps = false;
    protected $table = 'intervision_attendance';

    protected $fillable = ['session_id', 'participant_id', 'attended', 'marked_at', 'marked_by', 'notes'];

    protected function casts(): array
    {
        return [
            'attended' => 'boolean',
            'marked_at' => 'datetime',
        ];
    }

    public function session()
    {
        return $this->belongsTo(IntervisionSession::class, 'session_id');
    }

    public function participant()
    {
        return $this->belongsTo(IntervisionParticipant::class, 'participant_id');
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
