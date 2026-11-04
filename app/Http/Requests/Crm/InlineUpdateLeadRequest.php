<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InlineUpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = $this->user('admin');
        $lead = $this->route('lead');

        if (! $admin || ! $lead) {
            return false;
        }

        $field = $this->input('field');

        return match ($field) {
            'assigned_to' => $admin->can('assign', $lead),
            'lead_status', 'full_name' => $admin->can('update', $lead),
            default => false,
        };
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'field' => ['required', Rule::in(['lead_status', 'assigned_to', 'full_name'])],
            'value' => ['nullable'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $field = $this->input('field');
            $value = $this->input('value');

            if ($field === 'lead_status') {
                if (! \App\Support\LeadStatusCatalog::isValid(is_string($value) ? $value : null)) {
                    $validator->errors()->add('value', 'Invalid lead status.');
                }
            }

            if ($field === 'assigned_to' && $value !== null && $value !== '') {
                if (! is_numeric($value)) {
                    $validator->errors()->add('value', 'Invalid assignee.');
                }
            }

            if ($field === 'full_name') {
                if (trim((string) $value) === '') {
                    $validator->errors()->add('value', 'Name is required.');
                }
            }
        });
    }
}
