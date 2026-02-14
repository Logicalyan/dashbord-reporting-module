<?php

namespace App\Services\Reports;

use App\DTO\Base\BaseReportFilterDTO;
use App\DTO\Reports\CompanyReportFilterDTO;
use App\Models\Company;
use App\Services\Reports\Base\BaseReportService;
use Illuminate\Database\Eloquent\Builder;

class CompanyReportService extends BaseReportService
{
    protected function getModel(): string
    {
        return Company::class;
    }

    protected function getDateColumn(): string
    {
        return 'joined_at';
    }

    protected function applyCustomFilters(Builder $query, BaseReportFilterDTO $dto): void
    {
        if (!$dto instanceof CompanyReportFilterDTO) {
            return;
        }

        // Filter by status
        if ($dto->status) {
            $query->where('status', $dto->status);
        }

        // Filter active/inactive using scope
        if ($dto->activeOnly !== null) {
            if ($dto->activeOnly) {
                $query->active();
            } else {
                $query->inactive();
            }
        }
    }

    protected function transformDetailData($item): array
    {
        return [
            'company_name' => $item->name,
            'email' => $item->email,
            'status' => ucfirst($item->status),
            'joined_date' => $item->joined_at->format('Y-m-d'),
            'days_since_joined' => $item->joined_at->diffInDays(now()),
        ];
    }

    /**
     * Get status breakdown
     */
    public function getStatusBreakdown(CompanyReportFilterDTO $dto): array
    {
        return $this->getSummaryData($dto, 'status')
            ->pluck('total', 'status')
            ->mapWithKeys(fn($total, $status) => [ucfirst($status) => $total])
            ->toArray();
    }

    /**
     * Get company statistics
     */
    public function getStatistics(CompanyReportFilterDTO $dto): array
    {
        $query = $this->buildBaseQuery($dto);

        return [
            'total_companies' => $query->count(),
            'active_companies' => (clone $query)->active()->count(),
            'inactive_companies' => (clone $query)->inactive()->count(),
            'avg_days_since_joined' => round(
                (clone $query)->get()->avg(fn($c) => $c->joined_at->diffInDays(now()))
            ),
            'oldest_company' => (clone $query)->oldest('joined_at')->first()?->name,
            'newest_company' => (clone $query)->latest('joined_at')->first()?->name,
        ];
    }

    /**
     * Get monthly join trend
     */
    public function getMonthlyJoinTrend(CompanyReportFilterDTO $dto): array
    {
        $query = $this->buildBaseQuery($dto);

        return $query
            ->selectRaw('DATE_FORMAT(joined_at, "%Y-%m") as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }
}
