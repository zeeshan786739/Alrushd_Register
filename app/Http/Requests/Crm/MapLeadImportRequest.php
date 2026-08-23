<?php

namespace App\Http\Requests\Crm;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Support\LeadImportFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MapLeadImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('import leads') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $options = $this->input('options', []);
        if (isset($options['default_assigned_to']) && $options['default_assigned_to'] === '') {
            $options['default_assigned_to'] = null;
        }
        $this->merge(['options' => $options]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'selected_sheet' => ['nullable', 'string', 'max:255'],
            'header_row' => ['nullable', 'integer', 'min:1', 'max:50'],
            'mapping' => ['required', 'array'],
            'mapping.*' => ['required', 'string', Rule::in(array_keys(LeadImportFields::options()))],
            'options.duplicate_behavior' => ['required', Rule::in(['skip', 'create'])],
            'options.default_status' => ['required', Rule::enum(LeadStatus::class)],
            'options.default_priority' => ['required', Rule::enum(LeadPriority::class)],
            'options.default_assigned_to' => ['nullable', 'integer'],
            'options.source_label' => ['nullable', 'string', 'max:100'],
            'options.default_calling_code' => ['nullable', 'string', 'max:8'],
            'options.date_format' => ['nullable', 'string', 'max:32'],
        ];
    }
}
