<?php

namespace App\Http\Requests\Crm;

use App\Support\CrmOrgRules;
use App\Support\LeadCategorySchema;
use Illuminate\Foundation\Http\FormRequest;

class AssignLeadImportCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('import leads') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('lead_category_id') === '') {
            $this->merge(['lead_category_id' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        if (! LeadCategorySchema::ready()) {
            return ['lead_category_id' => ['nullable']];
        }

        return [
            'lead_category_id' => ['required', 'integer', CrmOrgRules::leadCategoryId(true)],
        ];
    }
}
