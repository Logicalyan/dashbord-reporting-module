<?php

use App\Http\Controllers\Api\AttendanceDataTableController;
use App\Http\Controllers\Api\CompanyDataTableController;
use App\Http\Controllers\Api\IntegrationController;
use App\Http\Controllers\AttendanceDashboardController;
use App\Http\Controllers\CompanyDashboardController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ReportPageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Route to redirect to Google's OAuth page
Route::get('auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');

Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::get('/attendance', [ReportPageController::class, 'attendance'])->name('attendance.index');
Route::get('/companies', [ReportPageController::class, 'company'])->name('companies.index');

// Dashboards
    Route::get('/attendance/dashboard', [AttendanceDashboardController::class, 'index'])
        ->name('attendance.dashboard');

    Route::get('/companies/dashboard', [CompanyDashboardController::class, 'index'])
        ->name('companies.dashboard');

Route::prefix('api')->middleware(['auth'])->group(function () {
    // DataTable endpoints
    Route::get('/attendance/datatable', [AttendanceDataTableController::class, 'index']);
    Route::get('/companies/datatable', [CompanyDataTableController::class, 'index']);

    Route::prefix('/integrations')->group(function () {
    Route::get('/', [IntegrationController::class, 'index']);
    Route::post('/connect', [IntegrationController::class, 'connect']);
    Route::delete('/{provider}', [IntegrationController::class, 'disconnect']);
    Route::post('/{provider}/test', [IntegrationController::class, 'test']);
    Route::put('/{provider}/sync-settings', [IntegrationController::class, 'updateSyncSettings']);
});
});



require __DIR__.'/settings.php';

