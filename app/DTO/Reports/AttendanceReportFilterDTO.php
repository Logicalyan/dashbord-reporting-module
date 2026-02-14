<?php

namespace App\DTO\Reports;

use App\DTO\Base\BaseReportFilterDTO;

class AttendanceReportFilterDTO extends BaseReportFilterDTO
{
    public function __construct(
        ?string $startDate = null,
        ?string $endDate = null,
        public ?string $status = null,
        public ?int $employeeId = null,
        public ?string $employeeName = null,
        public ?bool $activeEmployeesOnly = null,
        public ?float $minHours = null,
        public ?float $maxHours = null,
        public ?bool $overtimeOnly = null,
        ?string $sortBy = 'date',
        ?string $sortDir = 'desc'
    ) {
        parent::__construct($startDate, $endDate, $sortBy, $sortDir);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), array_filter([
            'status' => $this->status,
            'employee_id' => $this->employeeId,
            'employee_name' => $this->employeeName,
            'active_employees_only' => $this->activeEmployeesOnly,
            'min_hours' => $this->minHours,
            'max_hours' => $this->maxHours,
            'overtime_only' => $this->overtimeOnly,
        ], fn($value) => $value !== null));
    }
}
