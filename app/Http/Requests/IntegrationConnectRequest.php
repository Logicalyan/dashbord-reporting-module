<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IntegrationConnectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:hr_system,payroll_system'],
            'name' => ['nullable', 'string', 'max:255'],
            'api_url' => ['required', 'url', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'provider.in' => 'Invalid provider. Supported: hr_system, payroll_system',
            'api_url.url' => 'API URL must be a valid URL',
        ];
    }
}
