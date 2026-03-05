<?php

namespace App\Models;

use App\Enums\ParticipantRole;
use App\Enums\SlotStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    protected $fillable = [
        'creator_id',
        'task_id',
        'starts_at',
        'visibility',
        'blind_mode',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'blind_mode' => 'boolean',
        'status'     => SlotStatus::class,
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(SlotParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(SlotParticipant::class)->where('status', 'active');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(SlotInvitation::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SlotMessage::class)->orderBy('created_at');
    }

    /** Время окончания сессии (starts_at + duration_minutes задания) */
    public function endsAt(): \Carbon\Carbon
    {
        return $this->starts_at->addMinutes($this->task->duration_minutes);
    }

    /** Доступна ли запись (не менее 1 часа до начала, статус open) */
    public function isJoinable(): bool
    {
        return $this->status === SlotStatus::Open
            && now()->lt($this->starts_at->subHour());
    }

    /** Можно ли подключиться к видеозвонку (за 5 минут до начала) */
    public function isConnectable(): bool
    {
        return in_array($this->status, [SlotStatus::Full, SlotStatus::InProgress])
            && now()->gte($this->starts_at->subMinutes(5));
    }

    /** Занятые роли среди активных участников */
    public function takenRoles(): array
    {
        return $this->activeParticipants
            ->pluck('role')
            ->map(fn($r) => $r instanceof ParticipantRole ? $r->value : $r)
            ->toArray();
    }

    /** Свободные роли */
    public function availableRoles(): array
    {
        $all   = [ParticipantRole::Therapist->value, ParticipantRole::Client->value, ParticipantRole::Observer->value];
        $taken = $this->takenRoles();
        return array_values(array_diff($all, $taken));
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', SlotStatus::Open);
    }
}
