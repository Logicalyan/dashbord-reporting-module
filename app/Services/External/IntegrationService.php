<?php

namespace App\Services\External;

use App\Models\ExternalIntegration;
use App\Services\External\HrApiClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IntegrationService
{
    protected HrApiClient $hrClient;

    public function __construct(HrApiClient $hrClient)
    {
        $this->hrClient = $hrClient;
    }

    public function connect(
        int $userId,
        string $provider,
        string $apiUrl,
        string $email,
        string $password,
        ?string $name = null
    ): ExternalIntegration {
        try {
            // Normalize API URL
            $baseUrl = rtrim($apiUrl, '/');

            Log::info('Attempting to connect to external API', [
                'user_id' => $userId,
                'provider' => $provider,
                'api_url' => $baseUrl,
                'email' => $email,
            ]);

            // Set base URL and attempt login
            $this->hrClient->setBaseUrl($baseUrl);
            $token = $this->hrClient->login($email, $password);

            if (!$token) {
                throw new \Exception('Login successful but token not found in response');
            }

            // Default token expiry: 24 hours
            $expiresAt = now()->addHours(24);

            // Create or update integration
            $integration = ExternalIntegration::updateOrCreate(
                [
                    'user_id' => $userId,
                    'provider' => $provider,
                ],
                [
                    'name' => $name ?? ucfirst($provider) . ' Integration',
                    'api_url' => $baseUrl,
                    'api_email' => $email,
                    'api_token' => $token,
                    'token_expires_at' => $expiresAt,
                    'status' => 'connected',
                    'metadata' => [
                        'connected_at' => now()->toDateTimeString(),
                    ],
                    'error_message' => null,
                ]
            );

            // Cache the token
            $cacheMinutes = $expiresAt->diffInMinutes(now());
            Cache::put(
                "integration.{$userId}.{$provider}.token",
                $token,
                now()->addMinutes($cacheMinutes)
            );

            Log::info('Integration connected successfully', [
                'user_id' => $userId,
                'provider' => $provider,
                'integration_id' => $integration->id,
                'token_expires_at' => $expiresAt->toDateTimeString(),
            ]);

            return $integration;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // HTTP error from API
            $statusCode = $e->response?->status();
            $errorMessage = $e->response?->json('message')
                ?? $e->response?->json('error')
                ?? 'Authentication failed';

            Log::error('Integration connection failed - HTTP Error', [
                'user_id' => $userId,
                'provider' => $provider,
                'status_code' => $statusCode,
                'error' => $errorMessage,
                'response' => $e->response?->json(),
            ]);

            $this->createFailedIntegration(
                $userId,
                $provider,
                $name,
                $apiUrl,
                $email,
                "Authentication failed: {$errorMessage}"
            );

            throw new \Exception("Authentication failed: {$errorMessage}");
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Network/connection error
            $errorMessage = 'Unable to connect to API server';

            Log::error('Integration connection failed - Network Error', [
                'user_id' => $userId,
                'provider' => $provider,
                'api_url' => $apiUrl,
                'error' => $e->getMessage(),
            ]);

            $this->createFailedIntegration(
                $userId,
                $provider,
                $name,
                $apiUrl,
                $email,
                $errorMessage . ': ' . $apiUrl
            );

            throw new \Exception($errorMessage . '. Please check the API URL.');
        } catch (\Exception $e) {
            Log::error('Integration connection failed - General Error', [
                'user_id' => $userId,
                'provider' => $provider,
                'api_url' => $apiUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->createFailedIntegration(
                $userId,
                $provider,
                $name,
                $apiUrl,
                $email,
                $e->getMessage()
            );

            throw $e;
        }
    }

    /**
     * Create failed integration record
     */
    protected function createFailedIntegration(
        int $userId,
        string $provider,
        ?string $name,
        string $apiUrl,
        string $email,
        string $errorMessage
    ): void {
        ExternalIntegration::updateOrCreate(
            [
                'user_id' => $userId,
                'provider' => $provider,
            ],
            [
                'name' => $name ?? ucfirst($provider) . ' Integration',
                'api_url' => $apiUrl,
                'api_email' => $email,
                'status' => 'error',
                'error_message' => $errorMessage,
                'api_token' => null,
                'token_expires_at' => null,
            ]
        );
    }

    /**
     * Disconnect integration
     */
    public function disconnect(int $userId, string $provider): void
    {
        $integration = ExternalIntegration::where('user_id', $userId)
            ->where('provider', $provider)
            ->firstOrFail();

        $integration->update([
            'status' => 'disconnected',
            'api_token' => null,
            'token_expires_at' => null,
            'error_message' => null,
        ]);

        // Clear cache
        Cache::forget("integration.{$userId}.{$provider}.token");

        Log::info('Integration disconnected', [
            'user_id' => $userId,
            'provider' => $provider,
        ]);
    }

    /**
     * Test existing connection
     */
    public function testConnection(int $userId, string $provider)
    {
        $integration = ExternalIntegration::where('user_id', $userId)
            ->where('provider', $provider)
            ->firstOrFail();

        if (!$integration->api_token) {
            throw new \Exception('No token found. Please reconnect.');
        }

        try {
            $this->hrClient->setBaseUrl($integration->api_url);
            $user = $this->hrClient->withToken($integration->api_token)->testConnection();

            $integration->markAsConnected();

            Log::info('Connection test successful', [
                'user_id' => $userId,
                'provider' => $provider,
            ]);

            return $user;
        } catch (\Exception $e) {
            $integration->markAsError('Connection test failed: ' . $e->getMessage());

            Log::error('Connection test failed', [
                'user_id' => $userId,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Re-authenticate (refresh connection)
     */
    public function reauthenticate(
        int $userId,
        string $provider,
        string $password
    ): ExternalIntegration {
        $integration = ExternalIntegration::where('user_id', $userId)
            ->where('provider', $provider)
            ->firstOrFail();

        try {
            // Re-login with stored email and new password
            $this->hrClient->setBaseUrl($integration->api_url);
            $token = $this->hrClient->login($integration->api_email, $password);

            if (!$token) {
                throw new \Exception('Re-authentication failed: Token not received');
            }

            // Update token
            $expiresAt = now()->addHours(24);
            $integration->update([
                'api_token' => $token,
                'token_expires_at' => $expiresAt,
                'status' => 'connected',
                'error_message' => null,
            ]);

            // Update cache
            $cacheMinutes = $expiresAt->diffInMinutes(now());
            Cache::put(
                "integration.{$userId}.{$provider}.token",
                $token,
                now()->addMinutes($cacheMinutes)
            );

            Log::info('Re-authentication successful', [
                'user_id' => $userId,
                'provider' => $provider,
            ]);

            return $integration;
        } catch (\Exception $e) {
            $integration->markAsError('Re-authentication failed: ' . $e->getMessage());

            Log::error('Re-authentication failed', [
                'user_id' => $userId,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get active token for integration
     */
    public function getToken(int $userId, string $provider): ?string
    {
        // Try cache first
        $cached = Cache::get("integration.{$userId}.{$provider}.token");
        if ($cached) {
            return $cached;
        }

        // Get from database
        $integration = ExternalIntegration::where('user_id', $userId)
            ->where('provider', $provider)
            ->connected()
            ->first();

        if (!$integration || !$integration->hasValidToken()) {
            return null;
        }

        // Cache for next time
        $cacheMinutes = $integration->token_expires_at
            ? $integration->token_expires_at->diffInMinutes(now())
            : 1380; // 23 hours

        Cache::put(
            "integration.{$userId}.{$provider}.token",
            $integration->api_token,
            now()->addMinutes($cacheMinutes)
        );

        return $integration->api_token;
    }

    /**
     * Get base URL for integration
     */
    public function getBaseUrl(int $userId, string $provider): ?string
    {
        $integration = ExternalIntegration::where('user_id', $userId)
            ->where('provider', $provider)
            ->first();

        return $integration?->api_url;
    }

    /**
     * Get integration instance (for direct use)
     */
    public function getIntegration(int $userId, string $provider): ?ExternalIntegration
    {
        return ExternalIntegration::where('user_id', $userId)
            ->where('provider', $provider)
            ->first();
    }

    /**
     * Get configured HrApiClient for integration
     */
    public function getConfiguredClient(int $userId, string $provider): HrApiClient
    {
        $integration = ExternalIntegration::where('user_id', $userId)
            ->where('provider', $provider)
            ->connected()
            ->firstOrFail();

        if (!$integration->hasValidToken()) {
            throw new \Exception('Token expired. Please reconnect.');
        }

        return $this->hrClient
            ->setBaseUrl($integration->api_url)
            ->withToken($integration->api_token);
    }

    /**
     * Get all integrations for user
     */
    public function getUserIntegrations(int $userId): array
    {
        $integrations = ExternalIntegration::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $integrations->map(fn($int) => [
            'id' => $int->id,
            'provider' => $int->provider,
            'name' => $int->name,
            'status' => $int->status,
            'api_url' => $int->api_url,
            'api_email' => $int->api_email,
            'is_connected' => $int->isConnected(),
            'has_valid_token' => $int->hasValidToken(),
            'token_expires_at' => $int->token_expires_at?->format('Y-m-d H:i:s'),
            'last_synced_at' => $int->last_synced_at?->format('Y-m-d H:i:s'),
            'error_message' => $int->error_message,
            'created_at' => $int->created_at->format('Y-m-d H:i:s'),
        ])->toArray();
    }

    /**
     * Update sync settings
     */
    public function updateSyncSettings(
        int $userId,
        string $provider,
        array $settings
    ): ExternalIntegration {
        $integration = ExternalIntegration::where('user_id', $userId)
            ->where('provider', $provider)
            ->firstOrFail();

        $integration->update([
            'sync_settings' => $settings,
        ]);

        Log::info('Sync settings updated', [
            'user_id' => $userId,
            'provider' => $provider,
            'settings' => $settings,
        ]);

        return $integration;
    }

    /**
     * Check if user has active integration
     */
    public function hasActiveIntegration(int $userId, string $provider): bool
    {
        return ExternalIntegration::where('user_id', $userId)
            ->where('provider', $provider)
            ->connected()
            ->exists();
    }

    /**
     * Get integration status summary
     */
    public function getStatusSummary(int $userId): array
    {
        $integrations = ExternalIntegration::where('user_id', $userId)->get();

        return [
            'total' => $integrations->count(),
            'connected' => $integrations->where('status', 'connected')->count(),
            'disconnected' => $integrations->where('status', 'disconnected')->count(),
            'error' => $integrations->where('status', 'error')->count(),
            'integrations' => $integrations->map(fn($int) => [
                'provider' => $int->provider,
                'name' => $int->name,
                'status' => $int->status,
                'is_connected' => $int->isConnected(),
            ])->toArray(),
        ];
    }
}
