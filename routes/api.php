<?php

use App\Http\Controllers\Api\AttendanceReportController;
use App\Http\Controllers\Api\AttendanceSyncController;
use App\Http\Controllers\Api\CompanyReportController;
use App\Http\Controllers\Api\ExternalAttendanceController;
use App\Http\Controllers\Api\IntegrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->group(function () {
    Route::get('/attendance/export', [AttendanceReportController::class, 'export'])->name('reports.attendance.export');
    Route::get('/company/export', [CompanyReportController::class, 'export'])->name('reports.company.export');
});

Route::prefix('/external')->group(function () {
    // Authentication
    Route::post('/login', [ExternalAttendanceController::class, 'login']);
    Route::post('/logout', [ExternalAttendanceController::class, 'logout']);

    // Sync
    Route::post('/sync', [AttendanceSyncController::class, 'sync']);
    Route::post('/sync/yesterday', [AttendanceSyncController::class, 'syncYesterday']);
    Route::get('/sync/status', [AttendanceSyncController::class, 'status']);
    Route::get('/sync/statistics', [AttendanceSyncController::class, 'statistics']);
});

Route::middleware('auth:sanctum')->prefix('/integrations')->group(function () {
    Route::get('/', [IntegrationController::class, 'index']);
    Route::post('/connect', [IntegrationController::class, 'connect']);
    Route::delete('/{provider}', [IntegrationController::class, 'disconnect']);
    Route::post('/{provider}/test', [IntegrationController::class, 'test']);
    Route::put('/{provider}/sync-settings', [IntegrationController::class, 'updateSyncSettings']);
});
