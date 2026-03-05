<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_blocked',
        'blocked_reason',
        'timezone',
        'gender',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_blocked' => 'boolean',
        ];
    }

    public function isClient(): bool
    {
        return $this->role === 'CLIENT';
    }

    public function isPsychologist(): bool
    {
        return $this->role === 'PSYCHOLOGIST';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function psychologistProfile()
    {
        return $this->hasOne(PsychologistProfile::class);
    }

    public function cases()
    {
        return $this->hasMany(ClientCase::class, 'client_id');
    }

    public function caseResponses()
    {
        return $this->hasMany(CaseResponse::class, 'psychologist_id');
    }

    public function intervisionParticipations()
    {
        return $this->hasMany(IntervisionParticipant::class, 'psychologist_id');
    }

    // Тройки
    public function slotParticipations()
    {
        return $this->hasMany(SlotParticipant::class);
    }

    public function createdSlots()
    {
        return $this->hasMany(Slot::class, 'creator_id');
    }

    public function proposedTasks()
    {
        return $this->hasMany(Task::class, 'author_id');
    }

    public function triadNotifications()
    {
        return $this->hasMany(TriadNotification::class);
    }

    /** Счётчик троек по ролям (только completed + confirmed) */
    public function triadCounts(): array
    {
        $counts = $this->slotParticipations()
            ->where('status', 'active')
            ->where('confirmed_completion', true)
            ->whereHas('slot', fn($q) => $q->where('status', 'completed'))
            ->selectRaw('role, count(*) as cnt')
            ->groupBy('role')
            ->pluck('cnt', 'role')
            ->toArray();

        return [
            'therapist' => $counts['therapist'] ?? 0,
            'client'    => $counts['client'] ?? 0,
            'observer'  => $counts['observer'] ?? 0,
            'total'     => array_sum($counts),
        ];
    }

}
