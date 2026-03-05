<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicQuestion extends Model
{
    protected $table = 'public_questions';

    protected $fillable = ['author_name', 'author_email', 'question', 'status'];

    public function answers()
    {
        return $this->hasMany(PublicAnswer::class, 'question_id');
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', 'ANSWERED');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }
}
