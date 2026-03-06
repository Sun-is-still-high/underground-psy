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
        'diploma_scan_url',
        'diploma_number',
        'diploma_year',
        'diploma_institution',
        'diploma_verified',
        'diploma_rejection_comment',
        'can_consult',
        'work_format',
        'city',
        'languages',
    ];

    protected function casts(): array
    {
        return [
            'is_published'    => 'boolean',
            'diploma_verified' => 'boolean',
            'can_consult'     => 'boolean',
            'languages'       => 'array',
        ];
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

    public function methods()
    {
        return $this->belongsToMany(Method::class, 'psychologist_methods', 'psychologist_profile_id', 'method_id');
    }

    // TODO: перенести needsPriceConfirmation, confirmPrice, getProfileCompleteness
    // из _old_mvc/app/Models/PsychologistProfile.php в Laravel-стиле
}
