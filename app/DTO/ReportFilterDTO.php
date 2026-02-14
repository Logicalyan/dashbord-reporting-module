<?php
namespace App\DTO;

class ReportFilterDTO
{
    public function __construct(
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $status = null,
        public ?string $sortBy = 'date',
        public ?string $sortDir = 'desc'
    ) {}
}
