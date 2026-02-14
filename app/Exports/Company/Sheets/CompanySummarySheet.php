<?php

namespace App\Exports\Company\Sheets;

use App\Exports\Base\Sheets\BaseSummarySheet;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;

class CompanySummarySheet extends BaseSummarySheet
{
    protected function getSummaryConfig(): array
    {
        return [
            'column' => 'C', // Status column in detail sheet
            'values' => ['Active', 'Inactive'],
            'chart_type' => DataSeries::TYPE_PIECHART,
            'chart_title' => 'Company Status Distribution'
        ];
    }
}
