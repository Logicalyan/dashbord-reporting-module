<?php

namespace App\Exports\Attendance\Sheets;

use App\Exports\Base\Sheets\BaseSummarySheet;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;

class AttendanceSummarySheet extends BaseSummarySheet
{
    protected function getSummaryConfig(): array
    {
        return [
            'column' => 'F', // Status column in detail sheet
            'values' => ['Present', 'Absent', 'Late', 'Remote'],
            'chart_type' => DataSeries::TYPE_BARCHART,
            'chart_title' => 'Attendance Status Distribution'
        ];
    }
}
