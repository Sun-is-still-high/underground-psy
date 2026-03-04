<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProblemType extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'slug', 'description', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function cases()
    {
        return $this->hasMany(ClientCase::class, 'problem_type_id');
    }

    public function psychologistSpecializations()
    {
        return $this->hasMany(PsychologistSpecialization::class, 'problem_type_id');
    }
}
