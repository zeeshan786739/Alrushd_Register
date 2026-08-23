<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmLeadImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('import leads') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'confirm' => ['accepted'],
        ];
    }
}
