<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'status' => ['nullable', 'string', 'in:Present,Absent,Late,Remote'],
        ];
    }
}
