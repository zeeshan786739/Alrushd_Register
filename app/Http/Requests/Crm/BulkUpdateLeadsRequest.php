<?php

namespace App\Http\Requests\Crm;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateLeadsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = $this->user('admin');

        if (! $admin) {
            return false;
        }

        $field = $this->input('field');

        return match ($field) {
            'assigned_to' => $admin->can('assign leads'),
            'lead_status', 'priority' => $admin->can('update leads'),
            default => false,
        };
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'lead_ids' => ['required', 'array', 'min:1', 'max:100'],
            'lead_ids.*' => ['integer', 'min:1'],
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
                $exists = Admin::forCurrentOrganization()->whereKey((int) $value)->exists();
                if (! $exists) {
                    $validator->errors()->add('value', 'Invalid assignee.');
                }
            }
        });
    }
}
