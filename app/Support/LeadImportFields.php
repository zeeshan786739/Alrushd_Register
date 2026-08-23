<?php

namespace App\Support;

final class LeadImportFields
{
    public const IGNORE = 'ignore';

    public const CUSTOM = 'custom';

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::IGNORE => 'Ignore',
            'full_name' => 'Full name',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company' => 'Company',
            'city' => 'City',
            'province' => 'Province / state',
            'postal_code' => 'Postal code',
            'address' => 'Address',
            'lead_description' => 'Description',
            'selected_school' => 'Selected school',
            'advertising_platform' => 'Advertising platform',
            'campaign_name' => 'Campaign',
            'adset_name' => 'Ad set',
            'ad_name' => 'Ad / creative',
            'form_name' => 'Form name',
            'source_submitted_at' => 'Source date',
            'source_time' => 'Source time',
            'lead_status' => 'Lead status',
            'assigned_to_name' => 'Agent / owner name',
            'assigned_to_email' => 'Agent email',
            'notes' => 'Notes',
            'follow_up_date' => 'Follow-up date',
            'follow_up_time' => 'Follow-up time',
            self::CUSTOM => 'Additional / custom field',
        ];
    }

    /** @return array<int, string> */
    public static function uniqueTargets(): array
    {
        return array_values(array_filter(
            array_keys(self::options()),
            fn (string $key) => ! in_array($key, [self::IGNORE, self::CUSTOM], true)
        ));
    }
}
