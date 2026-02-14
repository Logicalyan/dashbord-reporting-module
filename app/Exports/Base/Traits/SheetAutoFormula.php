<?php
namespace App\Exports\Base\Traits;

trait SheetAutoFormula
{
    public function applyTotalFormula($event, string $column, int $startRow)
    {
        $lastRow = $event->sheet->getHighestRow();
        $event->sheet->setCellValue("A" . ($lastRow + 1), 'TOTAL');
        $event->sheet->setCellValue(
            "{$column}" . ($lastRow + 1),
            "=SUM({$column}{$startRow}:{$column}{$lastRow})"
        );
    }

    public function applyCountIF($event, string $range, string $criteria, string $targetCell)
    {
        $event->sheet->setCellValue($targetCell, "=COUNTIF({$range},\"{$criteria}\")");
    }
}
