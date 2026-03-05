<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use App\Enums\ParticipantRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotInvitation extends Model
{
    protected $fillable = [
        'slot_id',
        'inviter_id',
        'invitee_id',
        'proposed_role',
        'status',
    ];

    protected $casts = [
        'proposed_role' => ParticipantRole::class,
        'status'        => InvitationStatus::class,
    ];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }
}
