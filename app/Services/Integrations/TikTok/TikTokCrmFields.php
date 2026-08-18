<?php

namespace App\Services\Integrations\TikTok;

class TikTokCrmFields
{
    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company' => 'Company',
            'city' => 'City',
            'province' => 'Province / state',
            'postal_code' => 'Postal code',
            'selected_school' => 'Selected school',
            'lead_description' => 'Lead description',
        ];
    }

    /** @return array<int, string> */
    public static function allowed(): array
    {
        return array_keys(self::options());
    }

    public static function isAllowed(string $crmField): bool
    {
        return in_array($crmField, self::allowed(), true);
    }

    /**
     * Conservative default CRM field for a TikTok Instant Form field name.
     * Ambiguous custom questions return null (Do not map).
     */
    public static function suggest(string $tiktokField): ?string
    {
        $normalized = strtolower(trim(str_replace(['-', ' '], '_', $tiktokField)));
        $normalized = preg_replace('/_+/', '_', $normalized) ?? $normalized;

        return match ($normalized) {
            'email', 'email_address', 'e_mail' => 'email',
            'phone', 'phone_number', 'mobile', 'mobile_number', 'telephone' => 'phone',
            'first_name', 'firstname', 'given_name' => 'first_name',
            'last_name', 'lastname', 'surname', 'family_name' => 'last_name',
            'company', 'company_name' => 'company',
            'city' => 'city',
            'province', 'state', 'region' => 'province',
            'postal_code', 'postcode', 'zip', 'zip_code' => 'postal_code',
            'selected_school', 'school' => 'selected_school',
            default => null,
        };
    }
}
