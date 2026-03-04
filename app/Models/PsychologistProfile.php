<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsychologistProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'methods_description',
        'education',
        'experience_description',
        'hourly_rate_min',
        'hourly_rate_max',
        'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specializations()
    {
        return $this->hasMany(PsychologistSpecialization::class, 'profile_id');
    }

    public function problemTypes()
    {
        return $this->belongsToMany(ProblemType::class, 'psychologist_specializations', 'profile_id', 'problem_type_id');
    }
}
