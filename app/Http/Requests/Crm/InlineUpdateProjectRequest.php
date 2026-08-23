<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InlineUpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $admin = $this->user('admin');
        $project = $this->route('project');

        if (! $admin || ! $project) {
            return false;
        }

        return $admin->can('update', $project);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'field' => ['required', Rule::in(['status', 'priority', 'assigned_to'])],
            'value' => ['nullable'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $field = $this->input('field');
            $value = $this->input('value');

            if ($field === 'status') {
                if (! in_array($value, ['pending', 'in_progress', 'on_hold', 'completed', 'cancelled'], true)) {
                    $validator->errors()->add('value', 'Invalid project status.');
                }
            }

            if ($field === 'priority') {
                if (! in_array($value, ['low', 'medium', 'high', 'urgent'], true)) {
                    $validator->errors()->add('value', 'Invalid project priority.');
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
