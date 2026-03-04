<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCase extends Model
{
    protected $table = 'cases';

    protected $fillable = [
        'client_id',
        'problem_type_id',
        'title',
        'description',
        'is_anonymous',
        'status',
        'budget_type',
        'budget_amount',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function problemType()
    {
        return $this->belongsTo(ProblemType::class, 'problem_type_id');
    }

    public function responses()
    {
        return $this->hasMany(CaseResponse::class, 'case_id');
    }

    public function acceptedResponse()
    {
        return $this->hasOne(CaseResponse::class, 'case_id')->where('status', 'ACCEPTED');
    }
}
