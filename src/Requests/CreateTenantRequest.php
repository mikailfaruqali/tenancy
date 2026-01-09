<?php

namespace Snawbar\Tenancy\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TRUE;
    }

    public function rules(): array
    {
        return [
            'domain' => [
                'required',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
            ],
            // 'password' => [
            //     'required',
            //     'string',
            // ],
        ];
    }

    public function messages(): array
    {
        return [
            'domain.required' => 'Domain name is required.',
            'domain.min' => 'Domain must be at least 3 characters.',
            'domain.max' => 'Domain cannot exceed 63 characters.',
            'domain.regex' => 'Domain can only contain lowercase letters, numbers, and hyphens.',
            'password.required' => 'Password is required.',
        ];
    }
}
