<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use App\DTOs\External\AttendanceFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceSyncRequest;
use App\Http\Requests\ExternalApiLoginRequest;
use App\Services\AttendanceSyncService;
use App\Services\External\ExternalApiTokenService;
use Illuminate\Http\JsonResponse;

class ExternalAttendanceController extends Controller
{
    use ApiResponse;

    protected AttendanceSyncService $syncService;
    protected ExternalApiTokenService $tokenService;

    public function __construct(
        AttendanceSyncService $syncService,
        ExternalApiTokenService $tokenService
    ) {
        $this->syncService = $syncService;
        $this->tokenService = $tokenService;
    }

    /**
     * Login to external API
     */
    public function login(ExternalApiLoginRequest $request): JsonResponse
    {
        try {
            $token = $this->tokenService->loginAndSaveToken(
                $request->user()->id,
                $request->email,
                $request->password
            );

            return $this->success(
                ['token' => $token],
                'Successfully connected to HR system'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Failed to connect to HR system: ' . $e->getMessage(),
                401
            );
        }
    }

    /**
     * Sync attendance data
     */
    public function sync(AttendanceSyncRequest $request): JsonResponse
    {
        try {
            $dto = AttendanceFilterDTO::fromRequest($request->validated());

            $stats = $this->syncService->syncAttendance(
                $request->user()->id,
                $dto
            );

            return $this->success($stats, 'Attendance data synced successfully');
        } catch (\Exception $e) {
            return $this->error(
                'Sync failed: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get sync status
     */
    public function status(): JsonResponse
    {
        $status = $this->syncService->getSyncStatus(auth()->id());

        return $this->success($status);
    }

    /**
     * Logout from external API
     */
    public function logout(): JsonResponse
    {
        $this->tokenService->revokeToken(auth()->id());

        return $this->success(null, 'Disconnected from HR system');
    }
}
