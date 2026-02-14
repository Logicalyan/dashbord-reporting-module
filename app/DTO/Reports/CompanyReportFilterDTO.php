<?php

namespace App\DTO\Reports;

use App\DTO\Base\BaseReportFilterDTO;

class CompanyReportFilterDTO extends BaseReportFilterDTO
{
    public function __construct(
        ?string $startDate = null,
        ?string $endDate = null,
        public ?string $status = null,
        public ?bool $activeOnly = null,
        ?string $sortBy = 'joined_at',
        ?string $sortDir = 'desc'
    ) {
        parent::__construct($startDate, $endDate, $sortBy, $sortDir);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), array_filter([
            'status' => $this->status,
            'active_only' => $this->activeOnly,
        ], fn($value) => $value !== null));
    }
}
