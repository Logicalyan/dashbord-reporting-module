<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalSyncLog extends Model
{
    protected $fillable = [
        'user_id',
        'sync_type',
        'entity',
        'sync_date_from',
        'sync_date_to',
        'status',
        'stats',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'stats' => 'array',
        'sync_date_from' => 'date',
        'sync_date_to' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForEntity($query, string $entity)
    {
        return $query->where('entity', $entity);
    }
}
