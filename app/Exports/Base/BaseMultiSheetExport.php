<?php
namespace App\Exports\Base;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

abstract class BaseMultiSheetExport implements WithMultipleSheets
{
    protected $dto;
    protected $service;

    public function __construct($dto, $service)
    {
        $this->dto = $dto;
        $this->service = $service;
    }

    abstract public function sheets(): array;
}
