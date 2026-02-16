<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ExternalIntegration extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'name',
        'status',
        'api_url',
        'api_email',
        'api_token',
        'token_expires_at',
        'last_synced_at',
        'sync_settings',
        'metadata',
        'error_message',
    ];

    protected $casts = [
        'sync_settings' => 'array',
        'metadata' => 'array',
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'api_token', // Hide token in JSON responses
    ];

    // Encrypt/Decrypt token
    public function setApiTokenAttribute($value): void
    {
        $this->attributes['api_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeConnected($query)
    {
        return $query->where('status', 'connected');
    }

    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    // Helpers
    public function isConnected(): bool
    {
        return $this->status === 'connected' && $this->hasValidToken();
    }

    public function hasValidToken(): bool
    {
        if (!$this->api_token) {
            return false;
        }

        if ($this->token_expires_at && $this->token_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function markAsConnected(): void
    {
        $this->update([
            'status' => 'connected',
            'error_message' => null,
        ]);
    }

    public function markAsDisconnected($reason = null): void
    {
        $this->update([
            'status' => 'disconnected',
            'error_message' => $reason,
        ]);
    }

    public function markAsError(string $error): void
    {
        $this->update([
            'status' => 'error',
            'error_message' => $error,
        ]);
    }
}
