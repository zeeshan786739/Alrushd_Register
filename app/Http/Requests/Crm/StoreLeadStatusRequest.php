<?php

namespace App\Http\Requests\Crm;

use App\Support\CrmColorPalette;
use App\Support\LeadCategoryUi;
use App\Support\LeadStatusCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = $this->user('admin');

        return $admin && $admin->can('update leads');
    }

    protected function prepareForValidation(): void
    {
        $color = $this->input('color') ?: $this->input('tone');
        $normalized = CrmColorPalette::normalize(is_string($color) ? $color : null);
        if ($normalized) {
            $this->merge(['color' => $normalized, 'tone' => $normalized]);

            return;
        }

        if ($this->input('tone') === '') {
            $this->merge(['tone' => null]);
        }
        if ($this->input('color') === '') {
            $this->merge(['color' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        if (! LeadStatusCatalog::ready()) {
            return ['name' => ['required', 'string', 'max:100']];
        }

        $orgId = \App\Support\OrganizationContext::idOrFail();
        $legacyTones = array_merge(LeadCategoryUi::toneIds(), ['caution']);

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('crm_lead_statuses', 'name')->where(fn ($q) => $q->where('organization_id', $orgId)),
            ],
            'color' => [
                'nullable',
                'string',
                'max:32',
                function (string $attribute, mixed $value, \Closure $fail) use ($legacyTones): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if (CrmColorPalette::isHex((string) $value)) {
                        return;
                    }
                    if (in_array((string) $value, $legacyTones, true)) {
                        return;
                    }
                    $fail('Please pick a color from the palette.');
                },
            ],
            'tone' => ['nullable', 'string', 'max:32'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a status name.',
            'name.unique' => 'A status with this name already exists.',
        ];
    }
}
