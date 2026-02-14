<?php
namespace App\Exports\Base\Traits;

trait SheetProtect
{
    public function protectSheet($event, string $password = 'secret')
    {
        $sheet = $event->sheet->getDelegate();
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword($password);
        $sheet->getStyle($sheet->calculateWorksheetDimension())
              ->getProtection()->setLocked(false); // user tetap bisa edit data yang tidak dikunci
    }
}
