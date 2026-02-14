<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Base\BaseDashboardController;
use App\Services\Dashboard\CompanyDashboardService;
use Illuminate\Http\Request;
use Inertia\Response;

class CompanyDashboardController extends BaseDashboardController
{
    /**
     * @var CompanyDashboardService
     */
    protected $dashboardService;

    protected string $viewComponent = 'companies/dashboard';
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

    public function index(Request $request): Response
    {
        $response = parent::index($request);
        $activeTab = $request->query('tab', 'overview');

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
