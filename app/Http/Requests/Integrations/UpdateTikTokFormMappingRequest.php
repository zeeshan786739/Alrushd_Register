<?php

namespace App\Http\Requests\Integrations;

use App\Enums\LeadPriority;
use App\Services\Integrations\TikTok\TikTokCrmFields;
use App\Support\OrganizationContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTikTokFormMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('manage integrations') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('assigned_to') === '') {
            $this->merge(['assigned_to' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $organizationId = OrganizationContext::idOrFail();

        return [
            'lead_source_label' => ['required', 'string', 'max:255'],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('admins', 'id')->where(fn ($query) => $query->where('organization_id', $organizationId)),
            ],
            'priority' => ['required', Rule::in(array_keys(LeadPriority::options()))],
            'auto_create_lead' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'field_mapping' => ['nullable', 'array'],
            'field_mapping.*' => ['nullable', 'string', Rule::in(array_merge([''], TikTokCrmFields::allowed()))],
        ];
    }

    /** @return array<string, mixed> */
    public function mappingPayload(): array
    {
        $validated = $this->validated();
        $mapping = [];

        foreach ($validated['field_mapping'] ?? [] as $tiktokField => $crmField) {
            if (! is_string($tiktokField) || $tiktokField === '') {
                continue;
            }

            $mapping[$tiktokField] = is_string($crmField) && TikTokCrmFields::isAllowed($crmField)
                ? $crmField
                : '';
        }

        return [
            'lead_source_label' => $validated['lead_source_label'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'priority' => $validated['priority'],
            'auto_create_lead' => $this->boolean('auto_create_lead'),
            'is_active' => $this->boolean('is_active'),
            'field_mapping' => $mapping,
        ];
    }
}
