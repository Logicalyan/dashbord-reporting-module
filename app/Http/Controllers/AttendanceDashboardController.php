<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\BaseDashboardController;
use App\Services\Dashboard\AttendanceDashboardService;
use Illuminate\Http\Request;
use Inertia\Response;

class AttendanceDashboardController extends BaseDashboardController
{
    /**
     * @var AttendanceDashboardService
     */
    protected $dashboardService; // ✅ Type hint di sini OK

    protected string $viewComponent = 'attendance/dashboard';
    protected string $title = 'Attendance Dashboard';

    public function __construct(AttendanceDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    protected function getChartKeys(): array
    {
        return ['monthly_attendance'];
    }

    protected function getDefaultOverviewPreset(): string
    {
        return 'this_month';
    }

    protected function getDefaultComparePreset(): string
    {
        return 'this_month_vs_last_month';
    }

    public function index(Request $request): Response
    {
        $response = parent::index($request);
        $activeTab = $request->query('tab', 'overview');

        if ($activeTab === 'overview') {
            $filters = $this->getOverviewFilters($request);

            $response->with([
                'topEmployees' => $this->dashboardService->getTopEmployees($filters, 5),
                'avgHours' => $this->dashboardService->getAverageHours($filters),
                'overtimeStats' => $this->dashboardService->getOvertimeStats($filters),
            ]);
        }

        if ($activeTab === 'compare') {
            $compareFilters = $this->getCompareFilters($request);

            $response->with([
                'statusDistribution' => $this->dashboardService->getStatusDistribution(
                    $compareFilters['period1'],
                    $compareFilters['period2']
                ),
            ]);
        }

        return $response;
    }
}
