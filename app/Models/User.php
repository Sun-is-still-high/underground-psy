<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'is_blocked',
        'blocked_reason',
        'timezone',
        'gender',
        'provider',
        'provider_id',
        'provider_token',
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

    public function isPendingVerification(): bool
    {
        return $this->status === 'pending_verification';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
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

    public function isModerator(): bool
    {
        return $this->role === 'MODERATOR';
    }

    public function canModerate(): bool
    {
        return in_array($this->role, ['ADMIN', 'MODERATOR']);
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

    /** Количество посещённых интервизий */
    public function intervisionCount(): int
    {
        return \DB::table('intervision_attendance as ia')
            ->join('intervision_participants as ip', 'ip.id', '=', 'ia.participant_id')
            ->where('ip.psychologist_id', $this->id)
            ->where('ia.attended', true)
            ->count();
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
