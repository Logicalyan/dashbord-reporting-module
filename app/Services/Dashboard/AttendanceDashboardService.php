<?php

namespace App\Services\Dashboard;

use App\Models\Attendance;
use App\Services\Dashboard\Base\BaseDashboardService;

class AttendanceDashboardService extends BaseDashboardService
{
    protected string $model = Attendance::class;
    protected string $dateColumn = 'date';

    public function getKpiMetrics(): array
    {
        return [
            'total_records' => [
                'label' => 'Total Records',
            ],
            'present_count' => [
                'label' => 'Present',
                'accent' => 'text-green-600',
                'query' => fn($q) => $q->where('status', 'Present')->count(),
            ],
            'absent_count' => [
                'label' => 'Absent',
                'accent' => 'text-red-600',
                'query' => fn($q) => $q->where('status', 'Absent')->count(),
            ],
            'late_count' => [
                'label' => 'Late',
                'accent' => 'text-yellow-600',
                'query' => fn($q) => $q->where('status', 'Late')->count(),
            ],
            'remote_count' => [
                'label' => 'Remote',
                'accent' => 'text-blue-600',
                'query' => fn($q) => $q->where('status', 'Remote')->count(),
            ],
            'total_hours' => [
                'label' => 'Total Hours',
                'accent' => 'text-purple-600',
                'query' => fn($q) => $q->sum('hours'),
            ],
        ];
    }

    public function getChartConfigs(): array
    {
        return [
            'monthly_attendance' => [
                'label' => 'Monthly Attendance Trend',
                'period' => 'month',
                'type' => 'line',
            ],
            'daily_attendance' => [
                'label' => 'Daily Attendance',
                'period' => 'day',
                'type' => 'bar',
            ],
        ];
    }

    /**
     * Get top employees by hours
     * ✅ FIX: No cache because of relationships
     */
    public function getTopEmployees(?array $filters = null, int $limit = 5): array
    {
        $query = $this->getBaseQuery();
        $this->applyDateFilter($query, $filters);

        // ✅ Execute immediately without cache
        return $query
            ->select('employee_id')
            ->selectRaw('COUNT(*) as total_days')
            ->selectRaw('SUM(hours) as total_hours')
            ->selectRaw('SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present_count')
            ->with('employee:id,name,position')
            ->groupBy('employee_id')
            ->orderByDesc('total_hours')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'employee_name' => $item->employee->name ?? 'N/A',
                'position' => $item->employee->position ?? 'N/A',
                'total_days' => $item->total_days,
                'total_hours' => round($item->total_hours, 2),
                'present_count' => $item->present_count,
                'attendance_rate' => $item->total_days > 0
                    ? round(($item->present_count / $item->total_days) * 100, 2)
                    : 0,
            ])
            ->toArray();
    }

    /**
     * Get average hours per day
     * ✅ FIX: Execute directly
     */
    public function getAverageHours(?array $filters = null): float
    {
        $query = $this->getBaseQuery();
        $this->applyDateFilter($query, $filters);

        return round($query->avg('hours') ?? 0, 2);
    }

    /**
     * Get overtime statistics
     * ✅ FIX: Execute directly
     */
    public function getOvertimeStats(?array $filters = null): array
    {
        $query = $this->getBaseQuery();
        $this->applyDateFilter($query, $filters);

        return [
            'total_overtime' => round($query->sum('overtime') ?? 0, 2),
            'avg_overtime' => round($query->avg('overtime') ?? 0, 2),
            'employees_with_overtime' => $query->where('overtime', '>', 0)->distinct('employee_id')->count('employee_id'),
        ];
    }

    /**
     * Get status distribution for comparison
     * ✅ FIX: Execute directly
     */
    public function getStatusDistribution(?array $period1 = null, ?array $period2 = null): array
    {
        $statuses = ['Present', 'Absent', 'Late', 'Remote'];

        $distribution1 = [];
        $distribution2 = [];

        foreach ($statuses as $status) {
            if ($this->isValidPeriod($period1)) {
                $query1 = $this->getBaseQuery();
                $this->applyDateFilter($query1, $period1);
                $distribution1[$status] = $query1->where('status', $status)->count();
            }

            if ($this->isValidPeriod($period2)) {
                $query2 = $this->getBaseQuery();
                $this->applyDateFilter($query2, $period2);
                $distribution2[$status] = $query2->where('status', $status)->count();
            }
        }

        return [
            'period1' => $distribution1,
            'period2' => $distribution2,
        ];
    }
}
