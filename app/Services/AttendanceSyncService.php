<?php

namespace App\Services;

use App\DTOs\External\AttendanceFilterDTO;
use App\Models\Attendance;
use App\Models\ExternalSyncLog;
use App\Services\External\HrApiClient;
use App\Services\External\ExternalApiTokenService;
use App\Services\External\IntegrationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceSyncService
{
    public HrApiClient $hrClient;
    protected ExternalApiTokenService $tokenService;
    protected IntegrationService $integrationService;

    public function __construct(
        HrApiClient $hrClient,
        ExternalApiTokenService $tokenService,
        IntegrationService $integrationService
    ) {
        $this->hrClient = $hrClient;
        $this->tokenService = $tokenService;
        $this->integrationService = $integrationService;
    }

    /**
     * Sync attendance data from external API
     */
    public function syncAttendance(
        int $userId,
        AttendanceFilterDTO $dto,
        string $syncType = 'manual'
    ): array {
        // Check if integration exists and is connected
        if (!$this->integrationService->hasActiveIntegration($userId, 'hr_system')) {
            throw new \Exception('No active HR system integration. Please connect first.');
        }

        // Create sync log
        $syncLog = $this->createSyncLog($userId, $dto, $syncType);

        try {
            // Get configured client
            $client = $this->integrationService->getConfiguredClient($userId, 'hr_system');

            // Fetch data from external API
            $externalData = $client->getAttendance($dto);

            // Sync to local database
            $stats = $this->performSync($externalData, $syncLog);

            // Mark as completed
            $this->completeSyncLog($syncLog, $stats);

            // Update integration last sync time
            $integration = $this->integrationService->getIntegration($userId, 'hr_system');
            $integration->update(['last_synced_at' => now()]);

            return $stats;

        } catch (\Exception $e) {
            // Mark as failed
            $this->failSyncLog($syncLog, $e);

            throw $e;
        }
    }

    /**
     * Sync for yesterday (for cron job)
     */
    public function syncYesterday(?int $userId = null): array
    {
        $yesterday = Carbon::yesterday();

        $dto = new AttendanceFilterDTO(
            startDate: $yesterday->format('Y-m-d'),
            endDate: $yesterday->format('Y-m-d')
        );

        return $this->syncAttendance($userId, $dto, 'auto');
    }

    /**
     * Sync for date range (for manual sync)
     */
    public function syncDateRange(
        int $userId,
        Carbon $from,
        Carbon $to
    ): array {
        $dto = new AttendanceFilterDTO(
            startDate: $from->format('Y-m-d'),
            endDate: $to->format('Y-m-d')
        );

        return $this->syncAttendance($userId, $dto, 'manual');
    }

    /**
     * Perform actual sync to database
     */
    protected function performSync($externalData, ExternalSyncLog $syncLog): array
    {
        $stats = [
            'total' => $externalData->count(),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        DB::beginTransaction();
        try {
            foreach ($externalData as $record) {
                try {
                    $result = $this->syncSingleRecord($record);
                    $stats[$result]++;
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error('Failed to sync record', [
                        'record' => $record,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            Log::info('Attendance sync completed', [
                'sync_log_id' => $syncLog->id,
                'stats' => $stats,
            ]);

            return $stats;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Sync single attendance record
     */
    protected function syncSingleRecord(array $record): string
    {
        // Validate required fields
        if (empty($record['employee_id']) || empty($record['date'])) {
            return 'skipped';
        }

        // Find or create attendance
        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $record['employee_id'],
                'date' => $record['date'],
            ],
            [
                'status' => $record['status'] ?? 'Present',
                'check_in' => $record['check_in'] ?? null,
                'check_out' => $record['check_out'] ?? null,
                'hours' => $record['hours'] ?? 0,
                'overtime' => $record['overtime'] ?? 0,
                'source' => 'api_sync',
                'synced_at' => now(),
                'external_id' => $record['id'] ?? null,
            ]
        );

        return $attendance->wasRecentlyCreated ? 'created' : 'updated';
    }

    /**
     * Create sync log entry
     */
    protected function createSyncLog(
        ?int $userId,
        AttendanceFilterDTO $dto,
        string $syncType
    ): ExternalSyncLog {
        return ExternalSyncLog::create([
            'user_id' => $userId,
            'sync_type' => $syncType,
            'entity' => 'attendance',
            'sync_date_from' => $dto->startDate,
            'sync_date_to' => $dto->endDate,
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Complete sync log
     */
    protected function completeSyncLog(ExternalSyncLog $syncLog, array $stats): void
    {
        $syncLog->update([
            'status' => 'completed',
            'stats' => $stats,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark sync log as failed
     */
    protected function failSyncLog(ExternalSyncLog $syncLog, \Exception $e): void
    {
        $syncLog->update([
            'status' => 'failed',
            'error_message' => $e->getMessage(),
            'completed_at' => now(),
        ]);

        Log::error('Attendance sync failed', [
            'sync_log_id' => $syncLog->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Get valid token
     */
    protected function getValidToken(?int $userId, string $syncType): string
    {
        if ($syncType === 'auto') {
            // For cron job, use system token from env
            $token = config('services.external_api.system_token');

            if (!$token) {
                throw new \Exception('System API token not configured');
            }

            return $token;
        }

        // For manual sync, use user token
        if (!$userId) {
            throw new \Exception('User ID required for manual sync');
        }

        $token = $this->tokenService->getValidToken($userId);

        if (!$token) {
            throw new \Exception('No valid API token found. Please login first.');
        }

        return $token;
    }

    /**
     * Get sync status and history
     */
    public function getSyncStatus(?int $userId = null): array
    {
        $query = ExternalSyncLog::forEntity('attendance')
            ->latest()
            ->limit(10);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $recentSyncs = $query->get();

        $lastSuccessful = ExternalSyncLog::forEntity('attendance')
            ->completed()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->latest('completed_at')
            ->first();

        return [
            'has_token' => $userId ? $this->tokenService->getValidToken($userId) !== null : true,
            'last_sync_at' => $lastSuccessful?->completed_at,
            'last_sync_stats' => $lastSuccessful?->stats,
            'is_syncing' => ExternalSyncLog::forEntity('attendance')
                ->where('status', 'processing')
                ->exists(),
            'recent_syncs' => $recentSyncs->map(fn($log) => [
                'id' => $log->id,
                'type' => $log->sync_type,
                'status' => $log->status,
                'date_range' => [
                    'from' => $log->sync_date_from->format('Y-m-d'),
                    'to' => $log->sync_date_to->format('Y-m-d'),
                ],
                'stats' => $log->stats,
                'error' => $log->error_message,
                'started_at' => $log->started_at?->format('Y-m-d H:i:s'),
                'completed_at' => $log->completed_at?->format('Y-m-d H:i:s'),
                'duration' => $log->completed_at
                    ? $log->started_at->diffInSeconds($log->completed_at) . 's'
                    : null,
            ]),
        ];
    }

    /**
     * Get sync statistics
     */
    public function getSyncStatistics(Carbon $from, Carbon $to): array
    {
        $syncs = ExternalSyncLog::forEntity('attendance')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        return [
            'total_syncs' => $syncs->count(),
            'successful' => $syncs->where('status', 'completed')->count(),
            'failed' => $syncs->where('status', 'failed')->count(),
            'total_records_synced' => $syncs->sum(fn($log) => $log->stats['created'] ?? 0) +
                                     $syncs->sum(fn($log) => $log->stats['updated'] ?? 0),
            'avg_duration' => $syncs
                ->filter(fn($log) => $log->completed_at)
                ->avg(fn($log) => $log->started_at->diffInSeconds($log->completed_at)),
        ];
    }
}
