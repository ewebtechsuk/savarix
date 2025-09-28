<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarketingLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['required', 'string', 'max:255'],
            'company_size' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'preferred_demo_at' => ['required', 'date'],
            'timezone' => ['required', 'string', 'max:60'],
            'source' => ['nullable', 'string', 'max:120'],
            'utm' => ['nullable', 'array'],
            'utm.*' => ['nullable', 'string', 'max:255'],
            'tracking_session' => ['nullable', 'string', 'max:64'],
        ];
    }
}
