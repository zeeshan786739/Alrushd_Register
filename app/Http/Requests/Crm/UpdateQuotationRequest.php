<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('update quotations') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return (new StoreQuotationRequest)->rules();
    }
}
