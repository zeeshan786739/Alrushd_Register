<?php

namespace App\Http\Requests\Crm;

use App\Support\LeadCategorySchema;
use App\Support\LeadCategoryUi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = $this->user('admin');

        return $admin && ($admin->can('import leads') || $admin->can('create leads') || $admin->can('update leads'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('icon') === '') {
            $this->merge(['icon' => null]);
        }
        if ($this->input('tone') === '') {
            $this->merge(['tone' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        if (! LeadCategorySchema::ready()) {
            return ['name' => ['required', 'string', 'max:100']];
        }

        $orgId = \App\Support\OrganizationContext::idOrFail();

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('lead_categories', 'name')->where(fn ($q) => $q->where('organization_id', $orgId)),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:80', Rule::in(LeadCategoryUi::iconIds())],
            'tone' => ['nullable', 'string', 'max:32', Rule::in(LeadCategoryUi::toneIds())],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a category name.',
            'name.unique' => 'A category with this name already exists.',
            'icon.in' => 'Please choose an icon from the list.',
            'tone.in' => 'Please choose a color from the list.',
        ];
    }
}
