<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Traits\DashboardFilterPresets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

abstract class BaseDashboardController extends Controller
{
    use DashboardFilterPresets;

    /**
     * Dashboard service instance
     * Child classes should re-declare this with specific type
     *
     * @var BaseDashboardService
     */
    protected $dashboardService;

    protected string $viewComponent;
    protected string $title = 'Dashboard';

    /**
     * Get chart keys to load
     */
    abstract protected function getChartKeys(): array;

    /**
     * Override to set default preset key for overview
     */
    protected function getDefaultOverviewPreset(): string
    {
        return 'last_30_days';
    }

    /**
     * Override to set default preset key for comparison
     */
    protected function getDefaultComparePreset(): string
    {
        return 'this_month_vs_last_month';
    }

    /**
     * Get default overview filters using preset
     */
    protected function getDefaultOverviewFilters(): array
    {
        $presets = $this->getFilterPresets();
        $presetKey = $this->getDefaultOverviewPreset();

        return $presets[$presetKey] ?? $presets['last_30_days'];
    }

    /**
     * Get default compare filters using preset
     */
    protected function getDefaultCompareFilters(): array
    {
        $presets = $this->getComparisonPresets();
        $presetKey = $this->getDefaultComparePreset();

        return $presets[$presetKey] ?? $presets['this_month_vs_last_month'];
    }

    /**
     * Dashboard index
     */
    public function index(Request $request): Response
    {
        $activeTab = $request->query('tab', 'overview');

        $data = [
            'activeTab' => $activeTab,
            'kpiMetrics' => $this->getKpiMetricsConfig(),
            'chartConfigs' => $this->getChartConfigsForFrontend(),
            'filterPresets' => $this->getFilterPresetsForFrontend(),
            'comparisonPresets' => $this->getComparisonPresetsForFrontend(),
        ];

        // Tab Overview
        if ($activeTab === 'overview') {
            $filters = $this->getOverviewFilters($request);
            $data['filters'] = $filters;
            $data['summary'] = $this->dashboardService->getSummary($filters);
            $data['growth'] = $this->dashboardService->getGrowth($filters);

            $charts = [];
            foreach ($this->getChartKeys() as $key) {
                $charts[$key] = Inertia::defer(
                    fn() => $this->dashboardService->getChartData($key, $filters)
                );
            }
            $data['charts'] = $charts;
        }

        // Tab Compare
        if ($activeTab === 'compare') {
            $compareFilters = $this->getCompareFilters($request);
            $data['compareFilters'] = $compareFilters;

            $data['comparisons'] = Inertia::defer(
                fn() => $this->dashboardService->getAllComparisons(
                    $compareFilters['period1'],
                    $compareFilters['period2']
                )
            );

            $comparisonCharts = [];
            foreach ($this->getChartKeys() as $key) {
                $comparisonCharts[$key] = Inertia::defer(
                    fn() => $this->dashboardService->getComparisonChart(
                        $key,
                        $compareFilters['period1'],
                        $compareFilters['period2']
                    )
                );
            }
            $data['comparisonCharts'] = $comparisonCharts;
        }

        return Inertia::render($this->viewComponent, $data);
    }

    /**
     * Get overview filters from request
     */
    protected function getOverviewFilters(Request $request): array
    {
        // Check if preset is selected
        $preset = $request->query('preset');
        if ($preset) {
            $presets = $this->getFilterPresets();
            if (isset($presets[$preset])) {
                return $presets[$preset];
            }
        }

        // Otherwise use manual filters or defaults
        $defaults = $this->getDefaultOverviewFilters();

        return [
            'from' => $request->query('from', $defaults['from']),
            'to' => $request->query('to', $defaults['to']),
        ];
    }

    /**
     * Get compare filters from request
     */
    protected function getCompareFilters(Request $request): array
    {
        // Check if comparison preset is selected
        $preset = $request->query('compare_preset');
        if ($preset) {
            $presets = $this->getComparisonPresets();
            if (isset($presets[$preset])) {
                return $presets[$preset];
            }
        }

        // Otherwise use manual filters or defaults
        $defaults = $this->getDefaultCompareFilters();

        return [
            'period1' => [
                'from' => $request->query('period1_from', $defaults['period1']['from']),
                'to' => $request->query('period1_to', $defaults['period1']['to']),
            ],
            'period2' => [
                'from' => $request->query('period2_from', $defaults['period2']['from']),
                'to' => $request->query('period2_to', $defaults['period2']['to']),
            ],
        ];
    }

    /**
     * Get KPI metrics configuration for frontend
     *
     * CRITICAL: Calls dashboardService->getKpiMetrics()
     */
    protected function getKpiMetricsConfig(): array
    {
        // Ensure the method exists (it's abstract in BaseDashboardService)
        if (!method_exists($this->dashboardService, 'getKpiMetrics')) {
            return [];
        }

        $metrics = $this->dashboardService->getKpiMetrics();

        return collect($metrics)
            ->map(fn($config, $key) => [
                'key' => $key,
                'label' => $config['label'] ?? ucfirst(str_replace('_', ' ', $key)),
                'accent' => $config['accent'] ?? null,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get chart configurations for frontend
     *
     * CRITICAL: Calls dashboardService->getChartConfigs()
     */
    protected function getChartConfigsForFrontend(): array
    {
        // Ensure the method exists (it's abstract in BaseDashboardService)
        if (!method_exists($this->dashboardService, 'getChartConfigs')) {
            return [];
        }

        $configs = $this->dashboardService->getChartConfigs();

        return collect($this->getChartKeys())
            ->mapWithKeys(fn($key) => [
                $key => [
                    'label' => $configs[$key]['label'] ?? ucfirst(str_replace('_', ' ', $key)),
                    'type' => $configs[$key]['type'] ?? 'line',
                ]
            ])
            ->toArray();
    }

    /**
     * Get filter presets for frontend
     */
    protected function getFilterPresetsForFrontend(): array
    {
        return [
            ['value' => 'today', 'label' => 'Today'],
            ['value' => 'yesterday', 'label' => 'Yesterday'],
            ['value' => 'last_7_days', 'label' => 'Last 7 Days'],
            ['value' => 'last_14_days', 'label' => 'Last 14 Days'],
            ['value' => 'last_30_days', 'label' => 'Last 30 Days'],
            ['value' => 'last_90_days', 'label' => 'Last 90 Days'],
            ['value' => 'this_week', 'label' => 'This Week'],
            ['value' => 'last_week', 'label' => 'Last Week'],
            ['value' => 'this_month', 'label' => 'This Month'],
            ['value' => 'last_month', 'label' => 'Last Month'],
            ['value' => 'this_quarter', 'label' => 'This Quarter'],
            ['value' => 'last_quarter', 'label' => 'Last Quarter'],
            ['value' => 'this_year', 'label' => 'This Year'],
            ['value' => 'last_year', 'label' => 'Last Year'],
        ];
    }

    /**
     * Get comparison presets for frontend
     */
    protected function getComparisonPresetsForFrontend(): array
    {
        return [
            ['value' => 'today_vs_yesterday', 'label' => 'Today vs Yesterday'],
            ['value' => 'this_week_vs_last_week', 'label' => 'This Week vs Last Week'],
            ['value' => 'this_month_vs_last_month', 'label' => 'This Month vs Last Month'],
            ['value' => 'this_quarter_vs_last_quarter', 'label' => 'This Quarter vs Last Quarter'],
            ['value' => 'this_year_vs_last_year', 'label' => 'This Year vs Last Year'],
            ['value' => 'last_7_days_vs_previous_7_days', 'label' => 'Last 7 Days vs Previous 7 Days'],
            ['value' => 'last_30_days_vs_previous_30_days', 'label' => 'Last 30 Days vs Previous 30 Days'],
            ['value' => 'q1_vs_q2', 'label' => 'Q1 vs Q2'],
            ['value' => 'q2_vs_q3', 'label' => 'Q2 vs Q3'],
            ['value' => 'q3_vs_q4', 'label' => 'Q3 vs Q4'],
            ['value' => 'h1_vs_h2', 'label' => 'H1 vs H2'],
        ];
    }
}
