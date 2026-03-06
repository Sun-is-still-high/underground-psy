<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Method extends Model
{
    protected $fillable = ['name'];

    public function profiles()
    {
        return $this->belongsToMany(PsychologistProfile::class, 'psychologist_methods', 'method_id', 'psychologist_profile_id');
    }
}
