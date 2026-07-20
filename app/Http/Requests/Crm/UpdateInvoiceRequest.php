<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('update invoices') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return (new StoreInvoiceRequest)->rules();
    }
}
