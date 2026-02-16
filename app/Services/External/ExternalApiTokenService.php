<?php

namespace App\Services\External;

use App\Models\ExternalApiToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ExternalApiTokenService
{
    protected HrApiClient $client;

    public function __construct(HrApiClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get valid token for user (with caching)
     */
    public function getValidToken(int $userId): ?string
    {
        return Cache::remember(
            "external_api_token.{$userId}",
            now()->addMinutes(50), // Cache for 50 min if token valid for 1 hour
            function () use ($userId) {
                $tokenRecord = ExternalApiToken::where('user_id', $userId)
                    ->where('expired_at', '>', now())
                    ->first();

                return $tokenRecord?->access_token;
            }
        );
    }

    /**
     * Save new token
     */
    public function saveToken(int $userId, string $token, Carbon $expiresAt): ExternalApiToken
    {
        // Invalidate old tokens
        ExternalApiToken::where('user_id', $userId)->delete();

        $tokenRecord = ExternalApiToken::create([
            'user_id' => $userId,
            'access_token' => $token,
            'expired_at' => $expiresAt,
        ]);

        // Update cache
        Cache::put(
            "external_api_token.{$userId}",
            $token,
            $expiresAt->diffInMinutes(now())
        );

        return $tokenRecord;
    }

    /**
     * Login and save token
     */
    public function loginAndSaveToken(int $userId, string $email, string $password): string
    {
        $token = $this->client->login($email, $password);

        $this->saveToken(
            $userId,
            $token,
            now()->addHours(1) // Adjust based on your API token expiry
        );

        return $token;
    }

    /**
     * Revoke token
     */
    public function revokeToken(int $userId): void
    {
        ExternalApiToken::where('user_id', $userId)->delete();
        Cache::forget("external_api_token.{$userId}");
    }
}
