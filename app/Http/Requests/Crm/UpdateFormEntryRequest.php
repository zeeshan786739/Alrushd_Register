<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('update form submissions') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,approved,rejected'],
            'data' => ['nullable', 'array'],
        ];
    }
}
