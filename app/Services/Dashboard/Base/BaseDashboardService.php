<?php

namespace App\Services\Dashboard\Base;

use App\Helpers\DashboardHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

abstract class BaseDashboardService
{
    protected string $model;
    protected string $dateColumn = 'created_at';
    protected int $cacheTTL = 60;

    abstract public function getKpiMetrics(): array;
    abstract public function getChartConfigs(): array;

    /**
     * ======================
     * SUMMARY KPI
     * ======================
     */
    public function getSummary(?array $filters = null): array
    {
        // ✅ FIX: Execute query INSIDE cache callback
        return Cache::remember(
            $this->cacheKey('summary', $filters),
            $this->cacheTTL,
            function () use ($filters) {
                $result = [];
                $metrics = $this->getKpiMetrics();

                foreach ($metrics as $key => $config) {
                    $query = $this->getBaseQuery(); // ✅ Create fresh query
                    $this->applyDateFilter($query, $filters);

                    if (isset($config['query'])) {
                        $value = $config['query']($query); // ✅ Execute immediately
                    } else {
                        $value = $query->count(); // ✅ Execute immediately
                    }

                    $result[$key] = is_numeric($value) ? round($value, 2) : $value;
                }

                return $result; // ✅ Return only results, not query
            }
        );
    }

    /**
     * ======================
     * GROWTH (MoM)
     * ======================
     */
    public function getGrowth(?array $filters = null, string $period = 'month'): float
    {
        if (!$filters || empty($filters['from']) || empty($filters['to'])) {
            return 0;
        }

        // ✅ FIX: Don't cache this, just execute
        $from = Carbon::parse($filters['from']);
        $to = Carbon::parse($filters['to']);

        $current = $this->getBaseQuery()
            ->whereBetween($this->dateColumn, [$from, $to])
            ->count();

        $previousFrom = $period === 'month'
            ? $from->copy()->subMonth()
            : $from->copy()->subYear();

        $previousTo = $period === 'month'
            ? $to->copy()->subMonth()
            : $to->copy()->subYear();

        $previous = $this->getBaseQuery()
            ->whereBetween($this->dateColumn, [$previousFrom, $previousTo])
            ->count();

        return DashboardHelper::growth($previous, $current);
    }

    /**
     * ======================
     * CHART DATA
     * ======================
     */
    public function getChartData(string $chartKey, ?array $filters = null): array
    {
        $configs = $this->getChartConfigs();

        if (!isset($configs[$chartKey])) {
            throw new \InvalidArgumentException("Chart config '{$chartKey}' not found");
        }

        $config = $configs[$chartKey];
        $period = $config['period'] ?? 'month';

        // ✅ FIX: Cache the result, not the query
        return Cache::remember(
            $this->cacheKey("chart.{$chartKey}", $filters),
            $this->cacheTTL,
            function () use ($period, $filters, $config) {
                return $this->generateChartData($period, $filters, $config);
            }
        );
    }

    /**
     * Generate chart data based on period
     */
    protected function generateChartData(string $period, ?array $filters, array $config): array
    {
        $query = $this->getBaseQuery();

        if (isset($config['query'])) {
            $config['query']($query);
        }

        $this->applyDateFilter($query, $filters);

        $format = $this->getPeriodFormat($period);

        // ✅ Execute immediately, get results
        $data = $query
            ->selectRaw("DATE_FORMAT({$this->dateColumn}, ?) as period, COUNT(*) as total", [$format['sql']])
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->pluck('total', 'period');

        return $this->fillPeriods($data, $filters, $period, $format['carbon']);
    }

    /**
     * Fill missing periods with zeros
     */
    protected function fillPeriods($data, ?array $filters, string $period, string $format): array
    {
        if (!$filters || empty($filters['from']) || empty($filters['to'])) {
            return [];
        }

        $start = Carbon::parse($filters['from'])->startOf($period === 'month' ? 'month' : 'day');
        $end = Carbon::parse($filters['to'])->endOf($period === 'month' ? 'month' : 'day');

        $periods = collect();

        while ($start <= $end) {
            $key = $start->format($format);
            $periods->push([
                'label' => $this->formatLabel($start, $period),
                'value' => $data[$key] ?? 0,
            ]);

            $period === 'month' ? $start->addMonth() : $start->addDay();
        }

        return $periods->toArray();
    }

    /**
     * Get period format for SQL and Carbon
     */
    protected function getPeriodFormat(string $period): array
    {
        return match($period) {
            'day' => ['sql' => '%Y-%m-%d', 'carbon' => 'Y-m-d'],
            'month' => ['sql' => '%Y-%m', 'carbon' => 'Y-m'],
            'year' => ['sql' => '%Y', 'carbon' => 'Y'],
            default => ['sql' => '%Y-%m', 'carbon' => 'Y-m'],
        };
    }

    /**
     * Format label for display
     */
    protected function formatLabel(Carbon $date, string $period): string
    {
        return match($period) {
            'day' => $date->translatedFormat('d M Y'),
            'month' => $date->translatedFormat('M Y'),
            'year' => $date->format('Y'),
            default => $date->translatedFormat('M Y'),
        };
    }

    /**
     * ======================
     * COMPARE DATA dengan 2 periode berbeda
     * ======================
     */
    public function getCompareData(string $metric, ?array $period1 = null, ?array $period2 = null): array
    {
        $metrics = $this->getKpiMetrics();

        if (!isset($metrics[$metric])) {
            throw new \InvalidArgumentException("Metric '{$metric}' not found");
        }

        if (!$this->isValidPeriod($period1) || !$this->isValidPeriod($period2)) {
            return [];
        }

        // ✅ FIX: Execute queries immediately
        $query1 = $this->getBaseQuery();
        $this->applyDateFilter($query1, $period1);
        if (isset($metrics[$metric]['query'])) {
            $value1 = $metrics[$metric]['query']($query1);
        } else {
            $value1 = $query1->count();
        }

        $query2 = $this->getBaseQuery();
        $this->applyDateFilter($query2, $period2);
        if (isset($metrics[$metric]['query'])) {
            $value2 = $metrics[$metric]['query']($query2);
        } else {
            $value2 = $query2->count();
        }

        return [
            'period1' => [
                'value' => is_numeric($value1) ? round($value1, 2) : $value1,
                'from' => Carbon::parse($period1['from'])->format('d M Y'),
                'to' => Carbon::parse($period1['to'])->format('d M Y'),
            ],
            'period2' => [
                'value' => is_numeric($value2) ? round($value2, 2) : $value2,
                'from' => Carbon::parse($period2['from'])->format('d M Y'),
                'to' => Carbon::parse($period2['to'])->format('d M Y'),
            ],
            'difference' => $value2 - $value1,
            'growth' => DashboardHelper::growth($value1, $value2),
        ];
    }

    /**
     * Get all comparisons for configured metrics
     */
    public function getAllComparisons(?array $period1 = null, ?array $period2 = null): array
    {
        // ✅ FIX: Cache results, not queries
        return Cache::remember(
            $this->cacheKey('comparisons', ['p1' => $period1, 'p2' => $period2]),
            $this->cacheTTL,
            function () use ($period1, $period2) {
                $result = [];
                $metrics = $this->getKpiMetrics();

                foreach (array_keys($metrics) as $key) {
                    $result[$key] = $this->getCompareData($key, $period1, $period2);
                }

                return $result; // ✅ Return only results
            }
        );
    }

    /**
     * Get comparison chart data (period1 vs period2)
     */
    public function getComparisonChart(string $chartKey, ?array $period1 = null, ?array $period2 = null): array
    {
        $configs = $this->getChartConfigs();

        if (!isset($configs[$chartKey])) {
            throw new \InvalidArgumentException("Chart config '{$chartKey}' not found");
        }

        $config = $configs[$chartKey];
        $period = $config['period'] ?? 'month';

        if (!$this->isValidPeriod($period1) || !$this->isValidPeriod($period2)) {
            return [
                'period1' => [
                    'data' => [],
                    'label' => '',
                ],
                'period2' => [
                    'data' => [],
                    'label' => '',
                ],
            ];
        }

        // ✅ FIX: Don't cache query, execute and return results
        $data1 = $this->generateChartData($period, $period1, $config);
        $data2 = $this->generateChartData($period, $period2, $config);

        return [
            'period1' => [
                'data' => $data1,
                'label' => Carbon::parse($period1['from'])->format('d M Y') . ' - ' . Carbon::parse($period1['to'])->format('d M Y'),
            ],
            'period2' => [
                'data' => $data2,
                'label' => Carbon::parse($period2['from'])->format('d M Y') . ' - ' . Carbon::parse($period2['to'])->format('d M Y'),
            ],
        ];
    }

    /**
     * Validate if period has valid from and to dates
     */
    protected function isValidPeriod(?array $period): bool
    {
        return $period && !empty($period['from']) && !empty($period['to']);
    }

    /**
     * Get base query
     */
    protected function getBaseQuery(): Builder
    {
        return $this->model::query();
    }

    /**
     * Apply date filter
     */
    protected function applyDateFilter($query, ?array $filters): void
    {
        if (!$filters || empty($filters['from']) || empty($filters['to'])) {
            return;
        }

        $query->whereBetween(
            $this->dateColumn,
            [
                Carbon::parse($filters['from'])->startOfDay(),
                Carbon::parse($filters['to'])->endOfDay(),
            ]
        );
    }

    /**
     * Generate cache key
     */
    protected function cacheKey(string $type, ?array $filters): string
    {
        $suffix = $filters ? md5(json_encode($filters)) : 'all';
        $class = class_basename($this);

        return "dashboard.{$class}.{$type}.{$suffix}";
    }
}
