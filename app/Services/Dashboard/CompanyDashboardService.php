<?php

namespace App\Services\Dashboard;

use App\Models\Company;
use App\Services\Dashboard\Base\BaseDashboardService;
use Carbon\Carbon;

class CompanyDashboardService extends BaseDashboardService
{
    protected string $model = Company::class;
    protected string $dateColumn = 'joined_at';

    public function getKpiMetrics(): array
    {
        return [
            'total_companies' => [
                'label' => 'Total Companies',
            ],
            'active_companies' => [
                'label' => 'Active Companies',
                'accent' => 'text-green-600',
                'query' => fn($q) => $q->active()->count(),
            ],
            'inactive_companies' => [
                'label' => 'Inactive Companies',
                'accent' => 'text-red-600',
                'query' => fn($q) => $q->inactive()->count(),
            ],
        ];
    }

    public function getChartConfigs(): array
    {
        return [
            'monthly_growth' => [
                'label' => 'Company Growth (Monthly)',
                'period' => 'month',
                'type' => 'line',
            ],
        ];
    }

    /**
     * Get newest companies
     */
    public function getNewestCompanies(?array $filters = null, int $limit = 5): array
    {
        $query = $this->getBaseQuery();
        $this->applyDateFilter($query, $filters);

        return $query
            ->latest('joined_at')
            ->limit($limit)
            ->get()
            ->map(fn($company) => [
                'name' => $company->name,
                'email' => $company->email,
                'status' => ucfirst($company->status),
                'joined_at' => $company->joined_at->format('d M Y'),
                'days_ago' => $company->joined_at->diffInDays(now()),
            ])
            ->toArray();
    }

    /**
     * Get company growth rate
     */
    public function getGrowthRate(?array $filters = null): array
    {
        if (!$filters || empty($filters['from']) || empty($filters['to'])) {
            return [
                'current_period' => 0,
                'previous_period' => 0,
                'growth_rate' => 0,
            ];
        }

        $from = Carbon::parse($filters['from']);
        $to = Carbon::parse($filters['to']);
        $diff = $from->diffInDays($to);

        $current = $this->getBaseQuery()
            ->whereBetween($this->dateColumn, [$from, $to])
            ->count();

        $previousFrom = $from->copy()->subDays($diff + 1);
        $previousTo = $from->copy()->subDay();

        $previous = $this->getBaseQuery()
            ->whereBetween($this->dateColumn, [$previousFrom, $previousTo])
            ->count();

        return [
            'current_period' => $current,
            'previous_period' => $previous,
            'growth_rate' => $previous > 0
                ? round((($current - $previous) / $previous) * 100, 2)
                : ($current > 0 ? 100 : 0),
        ];
    }
}
