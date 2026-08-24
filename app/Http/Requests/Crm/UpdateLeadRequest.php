<?php

namespace App\Http\Requests\Crm;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Support\CrmOrgRules;
use App\Support\LeadCategorySchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('update leads') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('assigned_to') === '') {
            $this->merge(['assigned_to' => null]);
        }
        if ($this->input('lead_category_id') === '') {
            $this->merge(['lead_category_id' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [
            'title' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:255'],
            'lead_source' => ['nullable', 'string', 'max:100'],
            'lead_status' => ['required', Rule::enum(LeadStatus::class)],
            'priority' => ['required', Rule::enum(LeadPriority::class)],
            'assigned_to' => ['nullable', 'integer', CrmOrgRules::adminId()],
            'lead_description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'next_follow_up_date' => ['nullable', 'date'],
            'next_follow_up_time' => ['nullable'],
            'next_follow_up_type' => ['nullable', 'string', 'max:100'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];

        if (LeadCategorySchema::ready()) {
            $rules['lead_category_id'] = ['nullable', 'integer', CrmOrgRules::leadCategoryId()];
        }

        return $rules;
    }
}
