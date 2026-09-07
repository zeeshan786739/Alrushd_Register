<?php

namespace App\Support;

use App\Models\FormEntry;

final class FormEntryContact
{
    /** @return array{display_name: string, first_name: string, last_name: ?string, email: ?string, phone: ?string, company: ?string, preview: ?string} */
    public static function fromEntry(FormEntry $entry): array
    {
        $data = is_array($entry->data) ? $entry->data : [];
        $fields = $data['fields'] ?? $data;

        if (! is_array($fields)) {
            $fields = $data;
        }

        $firstName = self::pick($fields, ['first_name', 'fname', 'name']);
        $lastName = self::pick($fields, ['last_name', 'lname']);
        $fullName = self::pick($fields, ['full_name', 'fullname', 'student_name', 'applicant_name']);

        if (! $firstName && $fullName) {
            $parts = preg_split('/\s+/', trim((string) $fullName), 2) ?: [];
            $firstName = $parts[0] ?? null;
            $lastName = $lastName ?: ($parts[1] ?? null);
        }

        $firstName = $firstName ?: 'Unknown';
        $displayName = trim($firstName.' '.($lastName ?? '')) ?: 'Submission #'.$entry->id;

        $email = self::pick($fields, ['email', 'email_address', 'parent_email']);
        $phone = self::pick($fields, ['phone', 'mobile', 'mobile_number', 'telephone', 'contact_number']);
        $company = self::pick($fields, ['company', 'organisation', 'organization', 'school_name']);

        $preview = collect($fields)
            ->reject(fn ($value, $key) => in_array($key, [
                'first_name', 'last_name', 'fname', 'lname', 'name', 'full_name', 'fullname',
                'email', 'email_address', 'phone', 'mobile', 'mobile_number', 'company',
            ], true))
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->take(2)
            ->map(fn ($value, $key) => str_replace('_', ' ', ucfirst((string) $key)).': '.(is_array($value) ? json_encode($value) : $value))
            ->implode(' · ');

        return [
            'display_name' => $displayName,
            'first_name' => (string) $firstName,
            'last_name' => $lastName ? (string) $lastName : null,
            'email' => $email ? (string) $email : null,
            'phone' => $phone ? (string) $phone : null,
            'company' => $company ? (string) $company : null,
            'preview' => $preview ?: null,
        ];
    }

    /** @param  array<string, mixed>  $fields
     * @param  list<string>  $keys
     */
    private static function pick(array $fields, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $fields[$key] ?? null;
            if ($value !== null && $value !== '') {
                return is_scalar($value) ? (string) $value : null;
            }
        }

        return null;
    }
}
