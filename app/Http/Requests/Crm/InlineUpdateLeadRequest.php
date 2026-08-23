<?php

namespace App\Http\Requests\Crm;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
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
            'lead_status', 'priority' => $admin->can('update', $lead),
            default => false,
        };
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'field' => ['required', Rule::in(['lead_status', 'priority', 'assigned_to'])],
            'value' => ['nullable'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $field = $this->input('field');
            $value = $this->input('value');

            if ($field === 'lead_status') {
                if (! in_array($value, array_column(LeadStatus::cases(), 'value'), true)) {
                    $validator->errors()->add('value', 'Invalid lead status.');
                }
            }

            if ($field === 'priority') {
                if (! in_array($value, array_column(LeadPriority::cases(), 'value'), true)) {
                    $validator->errors()->add('value', 'Invalid priority.');
                }
            }

            if ($field === 'assigned_to' && $value !== null && $value !== '') {
                if (! is_numeric($value)) {
                    $validator->errors()->add('value', 'Invalid assignee.');
                }
            }
        });
    }
}
