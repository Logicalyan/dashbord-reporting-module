<?php

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceSyncRequest;
use App\Services\AttendanceSyncService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceSyncController extends Controller
{
    use ApiResponse;

    protected AttendanceSyncService $syncService;

    public function __construct(AttendanceSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Manual sync with date range
     */
    public function sync(AttendanceSyncRequest $request): JsonResponse
    {
        try {
            $from = Carbon::parse($request->start_date);
            $to = Carbon::parse($request->end_date);

            // Check if range is too large
            if ($from->diffInDays($to) > 90) {
                return $this->error('Date range cannot exceed 90 days', 422);
            }

            $stats = $this->syncService->syncDateRange(
                $request->user()->id,
                $from,
                $to
            );

            return $this->success($stats, 'Attendance data synced successfully');

        } catch (\Exception $e) {
            return $this->error('Sync failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sync yesterday only
     */
    public function syncYesterday(Request $request): JsonResponse
    {
        try {
            $stats = $this->syncService->syncYesterday($request->user()->id);

            return $this->success($stats, 'Yesterday attendance synced successfully');

        } catch (\Exception $e) {
            return $this->error('Sync failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get sync status and history
     */
    public function status(Request $request): JsonResponse
    {
        $status = $this->syncService->getSyncStatus($request->user()->id);

        return $this->success($status);
    }

    /**
     * Get sync statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $from = Carbon::parse($request->query('from', now()->subMonth()));
        $to = Carbon::parse($request->query('to', now()));

        $stats = $this->syncService->getSyncStatistics($from, $to);

        return $this->success($stats);
    }
}
