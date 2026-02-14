<?php

namespace App\DTO\Base;

abstract class BaseReportFilterDTO
{
    public function __construct(
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $sortBy = 'created_at',
        public ?string $sortDir = 'desc',
        public array $additionalFilters = []
    ) {}

    /**
     * Convert DTO to array for query building
     */
    public function toArray(): array
    {
        return array_filter([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'sort_by' => $this->sortBy,
            'sort_dir' => $this->sortDir,
        ], fn($value) => $value !== null);
    }

    /**
     * Check if date range is set
     */
    public function hasDateRange(): bool
    {
        return !empty($this->startDate) && !empty($this->endDate);
    }
}
