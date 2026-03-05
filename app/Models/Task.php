<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'author_id',
        'title',
        'description',
        'instruction_client',
        'instruction_therapist',
        'instruction_observer',
        'duration_minutes',
        'status',
        'moderation_comment',
        'moderated_by',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', TaskStatus::Approved);
    }

    public function scopePending($query)
    {
        return $query->where('status', TaskStatus::Pending);
    }
}
