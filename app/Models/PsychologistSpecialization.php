<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsychologistSpecialization extends Model
{
    public $timestamps = false;

    protected $fillable = ['profile_id', 'problem_type_id'];

    public function profile()
    {
        return $this->belongsTo(PsychologistProfile::class, 'profile_id');
    }

    public function problemType()
    {
        return $this->belongsTo(ProblemType::class, 'problem_type_id');
    }
}
