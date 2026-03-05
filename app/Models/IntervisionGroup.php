<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntervisionGroup extends Model
{
    protected $fillable = ['name', 'description', 'max_participants', 'is_active', 'created_by'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(IntervisionParticipant::class, 'group_id');
    }

    public function activeParticipants()
    {
        return $this->participants()->where('is_active', true);
    }

    public function sessions()
    {
        return $this->hasMany(IntervisionSession::class, 'group_id');
    }
}
