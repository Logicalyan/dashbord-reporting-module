<?php

namespace App\Exports\Attendance\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class SummarySheet implements FromArray, WithCharts, WithTitle
{
    protected int $lastRow; // jumlah baris DetailSheet

    public function __construct(int $lastRow)
    {
        $this->lastRow = $lastRow;
    }

    /**
     * Generate summary array dengan formula COUNTIF semi-dynamic
     */
    public function array(): array
    {
        $statuses = ['Present','Absent','Late','Remote'];
        $data = [];

        foreach ($statuses as $status) {
            $data[] = [
                $status,
                "=COUNTIF('Detail'!C2:C{$this->lastRow},\"{$status}\")"
            ];
        }

        return array_merge([['Status','Total']], $data);
    }

    /**
     * Generate chart dari summary
     */
    public function charts()
    {
        // Label untuk chart
        $labels = [
            new DataSeriesValues('String', 'Summary!$B$1', null, 1),
        ];

        // Kategori diambil dari kolom Status
        $categories = [
            new DataSeriesValues('String', "Summary!\$A\$2:\$A\$5", null, 4),
        ];

        // Nilai diambil dari kolom Total
        $values = [
            new DataSeriesValues('Number', "Summary!\$B\$2:\$B\$5", null, 4),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($values) - 1),
            $labels,
            $categories,
            $values
        );

        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $title = new Title('Attendance Summary');

        $chart = new Chart(
            'attendance_chart',
            $title,
            $legend,
            $plotArea
        );

        $chart->setTopLeftPosition('D2');
        $chart->setBottomRightPosition('L15');

        return $chart;
    }

    public function title(): string
    {
        return 'Summary';
    }
}
