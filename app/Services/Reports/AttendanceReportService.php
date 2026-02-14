<?php

namespace App\Services\Reports;

use App\DTO\Base\BaseReportFilterDTO;
use App\DTO\Reports\AttendanceReportFilterDTO;
use App\Models\Attendance;
use App\Services\Reports\Base\BaseReportService;
use Illuminate\Database\Eloquent\Builder;

class AttendanceReportService extends BaseReportService
{
    protected function getModel(): string
    {
        return Attendance::class;
    }

    protected function getDateColumn(): string
    {
        return 'date';
    }

    protected function applyCustomFilters(Builder $query, BaseReportFilterDTO $dto): void
    {
        if (!$dto instanceof AttendanceReportFilterDTO) {
            return;
        }

        // Always load employee relationship
        $query->with('employee');

        // Filter by status
        if ($dto->status) {
            $query->where('status', $dto->status);
        }

        // Filter by employee
        if ($dto->employeeId) {
            $query->where('employee_id', $dto->employeeId);
        }

        // Filter by employee name (search)
        if ($dto->employeeName) {
            $query->whereHas('employee', function ($q) use ($dto) {
                $q->where('name', 'like', "%{$dto->employeeName}%");
            });
        }

        // Filter by active employees only
        if ($dto->activeEmployeesOnly) {
            $query->whereHas('employee', function ($q) {
                $q->where('is_active', true);
            });
        }

        // Filter by minimum hours
        if ($dto->minHours !== null) {
            $query->where('hours', '>=', $dto->minHours);
        }

        // Filter by maximum hours
        if ($dto->maxHours !== null) {
            $query->where('hours', '<=', $dto->maxHours);
        }

        // Filter with overtime only
        if ($dto->overtimeOnly) {
            $query->where('overtime', '>', 0);
        }
    }

    protected function transformDetailData($item): array
    {
        return [
            'employee_name' => $item->employee->name ?? 'N/A',
            'employee_email' => $item->employee->email ?? 'N/A',
            'employee_position' => $item->employee->position ?? 'N/A',
            'date' => $item->date->format('Y-m-d'),
            'day_of_week' => $item->date->format('l'),
            'status' => $item->status,
            'check_in' => $item->check_in?->format('H:i') ?? '-',
            'check_out' => $item->check_out?->format('H:i') ?? '-',
            'hours' => $item->hours ?? 0,
            'overtime' => $item->overtime ?? 0,
            'total_hours' => ($item->hours ?? 0) + ($item->overtime ?? 0),
        ];
    }

    /**
     * Get status breakdown
     */
    public function getStatusBreakdown(AttendanceReportFilterDTO $dto): array
    {
        return $this->getSummaryData($dto, 'status')
            ->pluck('total', 'status')
            ->toArray();
    }

    /**
     * Get attendance statistics
     */
    public function getStatistics(AttendanceReportFilterDTO $dto): array
    {
        $query = $this->buildBaseQuery($dto);

        $stats = [
            'total_records' => $query->count(),
            'unique_employees' => (clone $query)->distinct('employee_id')->count('employee_id'),
            'unique_dates' => (clone $query)->distinct('date')->count('date'),
        ];

        // Get aggregates
        $aggregates = (clone $query)->selectRaw('
            SUM(hours) as total_hours,
            AVG(hours) as avg_hours,
            MAX(hours) as max_hours,
            MIN(hours) as min_hours,
            SUM(overtime) as total_overtime,
            AVG(overtime) as avg_overtime
        ')->first();

        $stats['total_hours'] = round($aggregates->total_hours ?? 0, 2);
        $stats['avg_hours'] = round($aggregates->avg_hours ?? 0, 2);
        $stats['max_hours'] = round($aggregates->max_hours ?? 0, 2);
        $stats['min_hours'] = round($aggregates->min_hours ?? 0, 2);
        $stats['total_overtime'] = round($aggregates->total_overtime ?? 0, 2);
        $stats['avg_overtime'] = round($aggregates->avg_overtime ?? 0, 2);

        // Status breakdown
        $statusCounts = $this->getStatusBreakdown($dto);
        $stats['present_count'] = $statusCounts['Present'] ?? 0;
        $stats['absent_count'] = $statusCounts['Absent'] ?? 0;
        $stats['late_count'] = $statusCounts['Late'] ?? 0;
        $stats['remote_count'] = $statusCounts['Remote'] ?? 0;

        return $stats;
    }

    /**
     * Get daily attendance trend
     */
    public function getDailyTrend(AttendanceReportFilterDTO $dto): array
    {
        $query = $this->buildBaseQuery($dto);

        return $query
            ->selectRaw('DATE_FORMAT(date, "%Y-%m-%d") as day, COUNT(*) as total, SUM(hours) as total_hours')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn($item) => [
                'date' => $item->day,
                'count' => $item->total,
                'hours' => round($item->total_hours, 2),
            ])
            ->toArray();
    }

    /**
     * Get employee attendance summary
     */
    public function getEmployeeSummary(AttendanceReportFilterDTO $dto): array
    {
        $query = $this->buildBaseQuery($dto);

        return $query
            ->select('employee_id')
            ->with('employee:id,name,email,position')
            ->selectRaw('
                COUNT(*) as total_days,
                SUM(hours) as total_hours,
                AVG(hours) as avg_hours,
                SUM(overtime) as total_overtime,
                SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = "Absent" THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = "Late" THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN status = "Remote" THEN 1 ELSE 0 END) as remote_count
            ')
            ->groupBy('employee_id')
            ->get()
            ->map(fn($item) => [
                'employee_name' => $item->employee->name,
                'employee_email' => $item->employee->email,
                'position' => $item->employee->position,
                'total_days' => $item->total_days,
                'total_hours' => round($item->total_hours, 2),
                'avg_hours' => round($item->avg_hours, 2),
                'total_overtime' => round($item->total_overtime, 2),
                'present_count' => $item->present_count,
                'absent_count' => $item->absent_count,
                'late_count' => $item->late_count,
                'remote_count' => $item->remote_count,
            ])
            ->toArray();
    }
}
