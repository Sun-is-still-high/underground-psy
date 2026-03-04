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
}
