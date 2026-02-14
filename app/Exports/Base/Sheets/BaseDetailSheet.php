<?php

namespace App\Exports\Base\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

abstract class BaseDetailSheet implements FromCollection, WithHeadings, WithEvents, WithTitle, WithStyles
{
    protected $service;
    protected $dto;
    protected $data;

    public function __construct($service, $dto)
    {
        $this->service = $service;
        $this->dto = $dto;
        $this->data = $this->loadData();
    }

    /**
     * Load data from service
     */
    abstract protected function loadData();

    /**
     * Get column definitions for the sheet
     */
    abstract protected function getColumns(): array;

    /**
     * Get statistics to display below the data
     */
    abstract protected function getStatistics(): array;

    /**
     * Transform data row
     */
    abstract protected function transformRow($item): array;

    public function collection()
    {
        return $this->data->map(fn($item) => $this->transformRow($item));
    }

    public function headings(): array
    {
        return array_keys($this->getColumns());
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
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = $sheet->getHighestRow();
                $columns = $this->getColumns();
                $lastColumn = $this->getColumnLetter(count($columns));

                // Auto filter
                $sheet->setAutoFilter("A1:{$lastColumn}1");

                // Auto size columns
                foreach (range('A', $lastColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Add separator
                $separatorRow = $lastDataRow + 1;
                $sheet->mergeCells("A{$separatorRow}:{$lastColumn}{$separatorRow}");
                $sheet->setCellValue("A{$separatorRow}", "STATISTICS");
                $sheet->getStyle("A{$separatorRow}:{$lastColumn}{$separatorRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F81BD']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);

                // Add statistics
                $this->addStatistics($sheet, $separatorRow + 1, $lastColumn, $lastDataRow);
            }
        ];
    }

    /**
     * Add statistics rows
     */
    protected function addStatistics(Worksheet $sheet, int $startRow, string $lastColumn, int $lastDataRow): void
    {
        $statistics = $this->getStatistics();
        $currentRow = $startRow;
        $labelColumns = ceil(count($this->getColumns()) / 2);
        $labelColumnLetter = $this->getColumnLetter($labelColumns);

        foreach ($statistics as $label => $config) {
            // Merge cells for label
            $sheet->mergeCells("A{$currentRow}:{$labelColumnLetter}{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", $label);

            // Merge cells for value
            $valueStartCol = $this->getColumnLetter($labelColumns + 1);
            $sheet->mergeCells("{$valueStartCol}{$currentRow}:{$lastColumn}{$currentRow}");

            // Set formula or value
            if (isset($config['formula'])) {
                $formula = str_replace('{lastRow}', $lastDataRow, $config['formula']);
                $sheet->setCellValue("{$valueStartCol}{$currentRow}", $formula);
            } elseif (isset($config['value'])) {
                $sheet->setCellValue("{$valueStartCol}{$currentRow}", $config['value']);
            }

            // Apply styling
            $sheet->getStyle("A{$currentRow}:{$lastColumn}{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFF00']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
            ]);

            $currentRow++;
        }
    }

    /**
     * Get column letter from number (1 = A, 2 = B, etc.)
     */
    protected function getColumnLetter(int $number): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($number);
    }

    public function title(): string
    {
        return 'Detail';
    }
}
