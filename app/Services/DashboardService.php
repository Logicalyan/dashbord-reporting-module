<?php

namespace App\Services;

use App\Helpers\DashboardHelper;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * ======================
     * SUMMARY KPI
     * ======================
     */
    public function getSummary(?array $filters = null): array
    {
        return Cache::remember(
            $this->cacheKey('summary', $filters),
            60,
            function () use ($filters) {

                $query = Company::query();

                $this->applyDateFilter($query, $filters);

                $total = $query->count();
                $active = (clone $query)->active()->count();

                return [
                    'total_companies'    => $total,
                    'active_companies'   => $active,
                    'inactive_companies' => $total - $active,
                ];
            }
        );
    }

    /**
     * ======================
     * GROWTH (MoM)
     * ======================
     */
    public function getMonthlyGrowth(?array $filters = null): float
    {
        if (! $filters || empty($filters['from']) || empty($filters['to'])) {
            return 0;
        }

        $from = Carbon::parse($filters['from']);
        $to   = Carbon::parse($filters['to']);

        $current = Company::whereBetween('joined_at', [
            $from,
            $to
        ])->count();

        $previous = Company::whereBetween('joined_at', [
            $from->copy()->subMonth(),
            $to->copy()->subMonth()
        ])->count();

        return DashboardHelper::growth($previous, $current);
    }



    /**
     * ======================
     * MONTHLY CHART
     * ======================
     */
    public function getMonthlyChart(?array $filters = null): array
    {
        $query = Company::query();

        $this->applyDateFilter($query, $filters);

        $data = $query
            ->selectRaw('DATE_FORMAT(joined_at, "%Y-%m") as period, COUNT(*) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->pluck('total', 'period');

        if (! $filters || empty($filters['from']) || empty($filters['to'])) {
            return [];
        }

        $start = Carbon::parse($filters['from'])->startOfMonth();
        $end   = Carbon::parse($filters['to'])->endOfMonth();

        $periods = collect();
        while ($start <= $end) {
            $key = $start->format('Y-m');
            $periods->push([
                'label' => $start->translatedFormat('M Y'),
                'value' => $data[$key] ?? 0,
            ]);
            $start->addMonth();
        }

        return $periods->toArray();
    }


    /**
     * ======================
     * FILTER HELPER
     * ======================
     */
    protected function applyDateFilter($query, ?array $filters): void
    {
        if (! $filters) {
            return;
        }

        if (! empty($filters['from']) && ! empty($filters['to'])) {
            $query->whereBetween(
                'joined_at',
                [
                    Carbon::parse($filters['from'])->startOfDay(),
                    Carbon::parse($filters['to'])->endOfDay(),
                ]
            );
        }
    }

    /**
     * ======================
     * CACHE KEY GENERATOR
     * ======================
     */
    protected function cacheKey(string $type, ?array $filters): string
    {
        $suffix = $filters ? md5(json_encode($filters)) : 'all';

        return "dashboard.$type.$suffix";
    }
}
