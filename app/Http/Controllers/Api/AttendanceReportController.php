<?php

namespace App\Http\Controllers\Api;

use App\DTO\Reports\AttendanceReportFilterDTO;
use App\Exports\Attendance\AttendanceReportExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\AttendanceReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    protected AttendanceReportService $service;

    public function __construct(AttendanceReportService $service)
    {
        $this->service = $service;
    }

    public function export(Request $request)
    {
        $dto = new AttendanceReportFilterDTO(
            startDate: $request->query('start_date'),
            endDate: $request->query('end_date'),
            status: $request->query('status'),
            employeeId: $request->query('employee_id') ? (int) $request->query('employee_id') : null,
            employeeName: $request->query('employee_name'),
            activeEmployeesOnly: $request->query('active_employees_only') === '1',
            minHours: $request->query('min_hours') ? (float) $request->query('min_hours') : null,
            maxHours: $request->query('max_hours') ? (float) $request->query('max_hours') : null,
            overtimeOnly: $request->query('overtime_only') === '1',
            sortBy: $request->query('sort_by', 'date'),
            sortDir: $request->query('sort_dir', 'desc')
        );

        return Excel::download(
            new AttendanceReportExport($dto, $this->service),
            'attendance-report-' . now()->format('Y-m-d-His') . '.xlsx',
            \Maatwebsite\Excel\Excel::XLSX,
            ['charts' => true]
        );
    }
}
