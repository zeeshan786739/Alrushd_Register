<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class UploadLeadImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('import leads') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $maxKb = (int) ceil(((int) config('lead_import.max_bytes', 10485760)) / 1024);

        return [
            'file' => ['required', 'file', 'max:'.$maxKb],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $file = $this->file('file');
            if (! $file) {
                return;
            }
            $name = strtolower($file->getClientOriginalName());
            if (! str_ends_with($name, '.xlsx') && ! str_ends_with($name, '.xls') && ! str_ends_with($name, '.csv')) {
                $validator->errors()->add('file', 'The file must be an Excel or CSV document.');
            }
        });
    }
}
