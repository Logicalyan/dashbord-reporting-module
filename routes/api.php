<?php

use App\Http\Controllers\Api\AttendanceReportController;
use App\Http\Controllers\Api\CompanyReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->group(function () {
    Route::get('/attendance/export', [AttendanceReportController::class, 'export'])->name('reports.attendance.export');
    Route::get('/company/export', [CompanyReportController::class, 'export'])->name('reports.company.export');
});
