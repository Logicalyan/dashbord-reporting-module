<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\BaseDashboardController;
use App\Services\Dashboard\CompanyDashboardService;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends BaseDashboardController
{
    // TAMBAH: Deklarasi ulang dengan type yang spesifik
    protected CompanyDashboardService $dashboardService;

    protected string $viewComponent = '/dashboard';
    protected string $title = 'Company Dashboard';

    public function __construct(CompanyDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    protected function getChartKeys(): array
    {
        return ['monthly_growth'];
    }

    protected function getDefaultOverviewPreset(): string
    {
        return 'this_year';
    }

    protected function getDefaultComparePreset(): string
    {
        return 'this_month_vs_last_month';
    }

    /**
     * Add custom data for company dashboard
     */
    public function index(Request $request): Response
    {
        $response = parent::index($request);
        $activeTab = $request->query('tab', 'overview');

        // Sekarang $this->dashboardService sudah dikenali sebagai CompanyDashboardService

        if ($activeTab === 'overview') {
            $filters = $this->getOverviewFilters($request);

            $response->with([
                'newestCompanies' => $this->dashboardService->getNewestCompanies($filters, 5),
                'growthRate' => $this->dashboardService->getGrowthRate($filters),
            ]);
        }

        return $response;
    }
}
