<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InlineUpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = $this->user('admin');
        $customer = $this->route('customer');

        if (! $admin || ! $customer) {
            return false;
        }

        return $admin->can('update', $customer);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'field' => ['required', Rule::in(['status', 'assigned_to'])],
            'value' => ['nullable'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $field = $this->input('field');
            $value = $this->input('value');

            if ($field === 'status') {
                if (! in_array($value, ['active', 'inactive', 'prospect'], true)) {
                    $validator->errors()->add('value', 'Invalid customer status.');
                }
            }

            if ($field === 'assigned_to' && $value !== null && $value !== '') {
                if (! is_numeric($value)) {
                    $validator->errors()->add('value', 'Invalid assignee.');
                }
            }
        });
    }
}
