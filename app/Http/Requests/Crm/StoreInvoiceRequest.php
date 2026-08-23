<?php

namespace App\Http\Requests\Crm;

use App\Models\Crm\Invoice;
use App\Models\Crm\Project;
use App\Models\Crm\Quotation;
use App\Support\CrmOrgRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('create invoices') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('project_id') === '') {
            $this->merge(['project_id' => null]);
        }
        if ($this->input('quotation_id') === '') {
            $this->merge(['quotation_id' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', CrmOrgRules::customerId()],
            'project_id' => ['nullable', 'integer', CrmOrgRules::projectId()],
            'quotation_id' => ['nullable', 'integer', CrmOrgRules::quotationId()],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:draft,sent,partially_paid,paid,overdue,cancelled'],
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
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $customerId = (int) $this->input('customer_id');
            $projectId = $this->input('project_id');
            $quotationId = $this->input('quotation_id');

            if ($projectId) {
                $belongsToCustomer = Project::forCurrentOrganization()
                    ->whereKey($projectId)
                    ->where('customer_id', $customerId)
                    ->exists();

                if (! $belongsToCustomer) {
                    $validator->errors()->add('project_id', 'The selected project does not belong to the selected customer.');
                }
            }

            if (! $quotationId) {
                return;
            }

            $quotation = Quotation::forCurrentOrganization()->whereKey($quotationId)->first();
            if (! $quotation) {
                return;
            }

            if ((int) $quotation->customer_id !== $customerId) {
                $validator->errors()->add('quotation_id', 'The selected quotation does not belong to the selected customer.');
            }

            if ($quotation->converted_invoice_id
                || Invoice::forCurrentOrganization()->where('quotation_id', $quotation->id)->exists()) {
                $validator->errors()->add('quotation_id', 'This quotation already has an invoice.');
            }
        });
    }
}
