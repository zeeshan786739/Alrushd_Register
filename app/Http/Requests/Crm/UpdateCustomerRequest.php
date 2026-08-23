<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('update customers') ?? false;
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
        return (new StoreCustomerRequest)->rules();
    }
}
