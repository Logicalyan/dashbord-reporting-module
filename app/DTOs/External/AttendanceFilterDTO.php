<?php

namespace App\DTOs\External;

class AttendanceFilterDTO
{
    public function __construct(
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly ?int $employeeId = null,
        public readonly ?string $status = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            employeeId: $data['employee_id'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'employee_id' => $this->employeeId,
            'status' => $this->status,
        ], fn($value) => $value !== null);
    }
}
