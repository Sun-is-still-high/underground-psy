<?php

namespace App\Models;

use App\Enums\ParticipantRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotParticipant extends Model
{
    protected $fillable = [
        'slot_id',
        'user_id',
        'role',
        'original_role',
        'source',
        'status',
        'confirmed_completion',
    ];

    protected $casts = [
        'role'                 => ParticipantRole::class,
        'original_role'        => ParticipantRole::class,
        'confirmed_completion' => 'boolean',
    ];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
