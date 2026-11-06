<?php

namespace App\Http\Requests\Crm;

use App\Support\CrmOrgRules;
use App\Support\LeadCategorySchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('create leads') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('assigned_to') === '') {
            $this->merge(['assigned_to' => null]);
        }
        if ($this->input('lead_category_id') === '') {
            $this->merge(['lead_category_id' => null]);
        }
        foreach (['year_group', 'admission_intake', 'contact_method'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
        // Forms always send this list, so a save that leaves it out never erases children.
        if (is_array($this->input('extra_year_groups'))) {
            $this->merge(\App\Support\LeadFormOptions::collapseChildren($this->input('year_group'), $this->input('extra_year_groups')));
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
            'year_group' => ['nullable', 'string', 'max:50', Rule::in(\App\Support\LeadFormOptions::yearGroups())],
            'extra_year_groups' => ['nullable', 'array', 'max:'.(\App\Support\LeadFormOptions::MAX_CHILDREN - 1)],
            'extra_year_groups.*' => ['string', 'max:50', Rule::in(\App\Support\LeadFormOptions::yearGroups())],
            'admission_intake' => ['nullable', 'string', 'max:30', Rule::in(\App\Support\LeadFormOptions::admissionIntakes())],
            'contact_method' => ['nullable', 'string', 'max:100', Rule::in(\App\Support\LeadFormOptions::contactMethods())],
            'lead_source' => ['nullable', 'string', 'max:100'],
            'lead_status' => ['required', \App\Support\LeadStatusCatalog::validationRule()],
            'assigned_to' => ['nullable', 'integer', CrmOrgRules::adminId()],
            'lead_description' => ['nullable', 'string'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];

        $rules += \App\Support\LeadSettings::customFieldRules();

        if (LeadCategorySchema::ready()) {
            $rules['lead_category_id'] = ['nullable', 'integer', CrmOrgRules::leadCategoryId()];
        }

        return $rules;
    }
}
