<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseResponse extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'case_id',
        'psychologist_id',
        'message',
        'proposed_price',
        'status',
        'responded_at',
    ];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function case()
    {
        return $this->belongsTo(ClientCase::class, 'case_id');
    }

    public function psychologist()
    {
        return $this->belongsTo(User::class, 'psychologist_id');
    }
}
