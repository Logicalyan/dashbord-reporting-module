<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IntegrationSyncSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'auto_sync_enabled' => ['boolean'],
            'sync_frequency' => ['string', 'in:hourly,daily,weekly'],
            'sync_time' => ['nullable', 'date_format:H:i'],
            'entities' => ['array'],
            'entities.*' => ['string', 'in:attendance,employees,payroll'],
        ];
    }
}
