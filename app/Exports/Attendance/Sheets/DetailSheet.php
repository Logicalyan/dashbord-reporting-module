<?php

namespace App\Exports\Attendance\Sheets;

use App\Exports\Base\Traits\SheetAutoFormula;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class DetailSheet implements FromCollection, WithHeadings, WithEvents, WithTitle
{
    use SheetAutoFormula;

    protected $data;

    public function __construct($service, $dto)
    {
        $this->data = $service->getDetailData($dto);
    }

    public function collection()
    {
        return $this->data->map(function ($item) {
            return [
                $item->employee->name ?? '',
                $item->date->format('Y-m-d'),
                $item->status,
                $item->hours,
            ];
        });
    }

    public function headings(): array
    {
        return ['Employee', 'Date', 'Status', 'Amount'];
    }

    public function title(): string
    {
        return 'Detail';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = $sheet->getHighestRow();

                // 1️⃣ Auto filter untuk sort by
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

                // 2️⃣ Style heading
                $sheet->getStyle('A1:D1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F81BD']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                ]);

                $start = $lastDataRow + 1;
                $sheet->mergeCells("A{$start}:D{$start}");
                $sheet->setCellValue("A{$start}", "Total");

                $sheet->getStyle("A{$start}:D{$start}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F81BD']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                ]);

                // 3️⃣ Baris Total vertikal mulai setelah data
                $startRow = $lastDataRow + 2;

                // Total Days
                $sheet->mergeCells("A{$startRow}:B{$startRow}");
                $sheet->setCellValue("A{$startRow}", "Total Days");
                $sheet->mergeCells("C{$startRow}:D{$startRow}");
                $sheet->setCellValue("C{$startRow}", "=COUNTA(UNIQUE(B2:B{$lastDataRow}))");

                // Total Data
                $row = $startRow + 1;
                $sheet->mergeCells("A{$row}:B{$row}");
                $sheet->setCellValue("A{$row}", "Total Data");
                $sheet->mergeCells("C{$row}:D{$row}");
                $sheet->setCellValue("C{$row}", "=COUNTA(A2:A{$lastDataRow})");

                // Total Hours
                $row = $startRow + 2;
                $sheet->mergeCells("A{$row}:B{$row}");
                $sheet->setCellValue("A{$row}", "Total Hours");
                $sheet->mergeCells("C{$row}:D{$row}");
                $sheet->setCellValue("C{$row}", "=SUM(D2:D{$lastDataRow})");

                // Avg Hours
                $row = $startRow + 3;
                $sheet->mergeCells("A{$row}:B{$row}");
                $sheet->setCellValue("A{$row}", "Avg Hours");
                $sheet->mergeCells("C{$row}:D{$row}");
                $sheet->setCellValue("C{$row}", "=AVERAGE(D2:D{$lastDataRow})");

                // 4️⃣ Styling semua baris Total
                $sheet->getStyle("A{$startRow}:D{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                ]);
            }
        ];
    }
}
