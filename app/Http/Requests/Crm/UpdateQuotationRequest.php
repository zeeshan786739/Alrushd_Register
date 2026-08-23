<?php

namespace App\Http\Requests\Crm;

use App\Models\Crm\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('update quotations') ?? false;
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
        return (new StoreQuotationRequest)->rules();
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
