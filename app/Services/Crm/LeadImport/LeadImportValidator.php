<?php

namespace App\Services\Crm\LeadImport;

use App\Enums\LeadImportRowStatus;

class LeadImportValidator
{
    /**
     * @param  array<string, mixed>  $fields
     * @param  array<int, string>  $warnings
     * @return array{status: string, errors: array<int, string>, warnings: array<int, string>}
     */
    public function validate(array $fields, array $warnings = []): array
    {
        $errors = [];
        $email = trim(mb_strtolower((string) ($fields['email'] ?? '')));
        $phoneDigits = preg_replace('/\D+/', '', (string) ($fields['phone'] ?? '')) ?? '';
        $name = trim((string) ($fields['first_name'] ?? '').' '.(string) ($fields['last_name'] ?? ''));
        $hasName = $name !== '' && strcasecmp($name, 'Unknown') !== 0;

        if ($email === '' && $phoneDigits === '' && ! $hasName) {
            $errors[] = 'Row has no name, email, or phone';
        }

        $status = $errors !== []
            ? LeadImportRowStatus::Invalid->value
            : ($warnings !== [] ? LeadImportRowStatus::Warning->value : LeadImportRowStatus::Ready->value);

        return [
            'status' => $status,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    public function comparisonEmail(?string $email): ?string
    {
        $email = trim(mb_strtolower((string) $email));

        return $email !== '' ? $email : null;
    }

    public function comparisonPhone(?string $phone): ?string
    {
        $phone = trim((string) $phone);
        if ($phone === '' || preg_match('/\p{L}/u', $phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return strlen($digits) >= 7 ? $digits : null;
    }
}
