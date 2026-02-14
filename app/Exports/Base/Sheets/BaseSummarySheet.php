<?php

namespace App\Exports\Base\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title as ChartTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

abstract class BaseSummarySheet implements FromArray, WithCharts, WithTitle, WithStyles
{
    protected string $detailSheetName = 'Detail';
    protected int $lastDetailRow;

    public function __construct(int $lastDetailRow)
    {
        $this->lastDetailRow = $lastDetailRow;
    }

    /**
     * Get summary configuration
     *
     * @return array [
     *   'column' => 'C', // Column in detail sheet to count
     *   'values' => ['Present', 'Absent', ...], // Values to count
     *   'chart_type' => DataSeries::TYPE_BARCHART,
     *   'chart_title' => 'Summary Chart'
     * ]
     */
    abstract protected function getSummaryConfig(): array;

    public function array(): array
    {
        $config = $this->getSummaryConfig();
        $data = [];

        foreach ($config['values'] as $value) {
            $data[] = [
                $value,
                "=COUNTIF('{$this->detailSheetName}'!{$config['column']}2:{$config['column']}{$this->lastDetailRow},\"{$value}\")"
            ];
        }

        return array_merge([['Category', 'Total']], $data);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function charts()
    {
        $config = $this->getSummaryConfig();
        $rowCount = count($config['values']);

        $labels = [
            new DataSeriesValues('String', $this->title() . '!$B$1', null, 1),
        ];

        $categories = [
            new DataSeriesValues('String', $this->title() . "!\$A\$2:\$A\$" . ($rowCount + 1), null, $rowCount),
        ];

        $values = [
            new DataSeriesValues('Number', $this->title() . "!\$B\$2:\$B\$" . ($rowCount + 1), null, $rowCount),
        ];

        $series = new DataSeries(
            $config['chart_type'] ?? DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($values) - 1),
            $labels,
            $categories,
            $values
        );

        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        $title = new ChartTitle($config['chart_title'] ?? 'Summary Chart');

        $chart = new Chart(
            'summary_chart',
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
