<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntegrationConnectRequest;
use App\Http\Requests\IntegrationSyncSettingsRequest;
use App\Services\External\IntegrationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    use ApiResponse;

    protected IntegrationService $integrationService;

    public function __construct(IntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Get all integrations for current user
     */
    public function index(Request $request): JsonResponse
    {
        $integrations = $this->integrationService->getUserIntegrations(
            $request->user()->id
        );

        return $this->success($integrations);
    }

    /**
     * Connect to external system
     */
    public function connect(IntegrationConnectRequest $request): JsonResponse
    {
        try {
            $integration = $this->integrationService->connect(
                $request->user()->id,
                $request->provider,
                $request->api_url,
                $request->email,
                $request->password,
                $request->name
            );

            return $this->success(
                [
                    'id' => $integration->id,
                    'provider' => $integration->provider,
                    'name' => $integration->name,
                    'status' => $integration->status,
                    'is_connected' => $integration->isConnected(),
                ],
                'Successfully connected to ' . $integration->name
            );

        } catch (\Exception $e) {
            return $this->error(
                'Connection failed: ' . $e->getMessage(),
                422
            );
        }
    }

    /**
     * Disconnect integration
     */
    public function disconnect(Request $request, string $provider): JsonResponse
    {
        try {
            $this->integrationService->disconnect(
                $request->user()->id,
                $provider
            );

            return $this->success(null, 'Successfully disconnected');

        } catch (\Exception $e) {
            return $this->error('Disconnect failed: ' . $e->getMessage(), 422);
        }
    }

    /**
     * Test connection
     */
    public function test(Request $request, string $provider): JsonResponse
    {
        try {
            $user = $this->integrationService->testConnection(
                $request->user()->id,
                $provider
            );

            return $this->success($user, 'Connection test successful');

        } catch (\Exception $e) {
            return $this->error('Connection test failed: ' . $e->getMessage(), 422);
        }
    }

    /**
     * Update sync settings
     */
    public function updateSyncSettings(
        IntegrationSyncSettingsRequest $request,
        string $provider
    ): JsonResponse {
        try {
            $integration = $this->integrationService->updateSyncSettings(
                $request->user()->id,
                $provider,
                $request->validated()
            );

            return $this->success(
                ['sync_settings' => $integration->sync_settings],
                'Sync settings updated'
            );

        } catch (\Exception $e) {
            return $this->error('Update failed: ' . $e->getMessage(), 422);
        }
    }
}
