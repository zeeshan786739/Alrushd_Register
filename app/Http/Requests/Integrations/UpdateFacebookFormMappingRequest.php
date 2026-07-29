<?php

namespace App\Http\Requests\Integrations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFacebookFormMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('manage integrations') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'internal_label' => ['required', 'string', 'max:255'],
            'lead_source_label' => ['nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'integer', 'exists:admins,id'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'auto_create_lead' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'auto_create_lead' => $this->boolean('auto_create_lead'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
