<?php

namespace App\Http\Requests\Crm;

use App\Models\Crm\Project;
use App\Support\CrmOrgRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('create quotations') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('project_id') === '') {
            $this->merge(['project_id' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', CrmOrgRules::customerId()],
            'project_id' => ['nullable', 'integer', CrmOrgRules::projectId()],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:draft,sent,accepted,rejected,expired'],
            'terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $customerId = $this->input('customer_id');
            $projectId = $this->input('project_id');

            if (! $customerId || ! $projectId || $validator->errors()->isNotEmpty()) {
                return;
            }

            $belongsToCustomer = Project::forCurrentOrganization()
                ->whereKey($projectId)
                ->where('customer_id', $customerId)
                ->exists();

            if (! $belongsToCustomer) {
                $validator->errors()->add('project_id', 'The selected project does not belong to the selected customer.');
            }
        });
    }
}
