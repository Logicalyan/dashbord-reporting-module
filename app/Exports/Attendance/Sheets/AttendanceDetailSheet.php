<?php

namespace App\Exports\Attendance\Sheets;

use App\Exports\Base\Sheets\BaseDetailSheet;

class AttendanceDetailSheet extends BaseDetailSheet
{
    protected function loadData()
    {
        return collect($this->service->getDetailData($this->dto));
    }

    protected function getColumns(): array
    {
        return [
            'Employee Name' => 'employee_name',
            'Email' => 'employee_email',
            'Position' => 'employee_position',
            'Date' => 'date',
            'Day' => 'day_of_week',
            'Status' => 'status',
            'Check In' => 'check_in',
            'Check Out' => 'check_out',
            'Hours' => 'hours',
            'Overtime' => 'overtime',
            'Total Hours' => 'total_hours',
        ];
    }

    protected function getStatistics(): array
    {
        return [
            'Total Records' => ['formula' => '=COUNTA(A2:A{lastRow})'],
            'Total Days' => ['formula' => '=COUNTA(UNIQUE(D2:D{lastRow}))'],
            'Total Employees' => ['formula' => '=COUNTA(UNIQUE(A2:A{lastRow}))'],
            'Total Hours' => ['formula' => '=SUM(I2:I{lastRow})'],
            'Total Overtime' => ['formula' => '=SUM(J2:J{lastRow})'],
            'Total All Hours' => ['formula' => '=SUM(K2:K{lastRow})'],
            'Avg Hours/Day' => ['formula' => '=AVERAGE(I2:I{lastRow})'],
            'Avg Overtime/Day' => ['formula' => '=AVERAGE(J2:J{lastRow})'],
            'Max Hours' => ['formula' => '=MAX(I2:I{lastRow})'],
            'Min Hours' => ['formula' => '=MIN(I2:I{lastRow})'],
            'Present Count' => ['formula' => '=COUNTIF(F2:F{lastRow},"Present")'],
            'Absent Count' => ['formula' => '=COUNTIF(F2:F{lastRow},"Absent")'],
            'Late Count' => ['formula' => '=COUNTIF(F2:F{lastRow},"Late")'],
            'Remote Count' => ['formula' => '=COUNTIF(F2:F{lastRow},"Remote")'],
        ];
    }

    protected function transformRow($item): array
    {
        return [
            $item['employee_name'],
            $item['employee_email'],
            $item['employee_position'],
            $item['date'],
            $item['day_of_week'],
            $item['status'],
            $item['check_in'],
            $item['check_out'],
            $item['hours'],
            $item['overtime'],
            $item['total_hours'],
        ];
    }
}
