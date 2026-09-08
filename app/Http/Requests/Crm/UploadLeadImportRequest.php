<?php

namespace App\Http\Requests\Crm;

use App\Support\CrmOrgRules;
use App\Support\LeadCategorySchema;
use Illuminate\Foundation\Http\FormRequest;

class UploadLeadImportRequest extends FormRequest
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
        $maxKb = (int) ceil(((int) config('lead_import.max_bytes', 10485760)) / 1024);

        $rules = [
            'file' => ['required', 'file', 'max:'.$maxKb],
        ];

        if (LeadCategorySchema::ready()) {
            $rules['lead_category_id'] = ['required', 'integer', CrmOrgRules::leadCategoryId(true)];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'lead_category_id.required' => 'Please select or create a lead category before uploading.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $file = $this->file('file');
            if (! $file) {
                return;
            }
            $name = strtolower($file->getClientOriginalName());
            if (! str_ends_with($name, '.xlsx') && ! str_ends_with($name, '.xls') && ! str_ends_with($name, '.csv')) {
                $validator->errors()->add('file', 'The file must be an Excel or CSV document.');
            }
        });
    }
}
