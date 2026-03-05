<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicAnswer extends Model
{
    protected $table = 'public_answers';

    protected $fillable = ['question_id', 'psychologist_id', 'answer'];

    public function psychologist()
    {
        return $this->belongsTo(User::class, 'psychologist_id');
    }
}
