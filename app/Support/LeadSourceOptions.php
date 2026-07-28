<?php

namespace App\Support;

final class LeadSourceOptions
{
    /** @return array<string, string> */
    public static function filterOptions(): array
    {
        return [
            'facebook_lead_ads' => 'Facebook Lead Ads',
            'student_admission' => 'Student Admission',
            'form_submission' => 'Form Submission',
            'manual' => 'Manual',
        ];
    }

    public static function label(?string $source): string
    {
        if (! $source) {
            return 'Unknown';
        }

        return self::filterOptions()[$source] ?? ucwords(str_replace('_', ' ', $source));
    }

    public static function icon(?string $source): ?string
    {
        return match ($source) {
            'facebook_lead_ads' => 'logos:facebook',
            'student_admission' => 'solar:square-academic-cap-linear',
            'form_submission' => 'solar:inbox-in-linear',
            'manual' => 'solar:pen-linear',
            default => 'solar:link-linear',
        };
    }

    public static function badgeClass(?string $source): string
    {
        return match ($source) {
            'facebook_lead_ads' => 'bg-primary-50 text-primary-600',
            'student_admission' => 'bg-success-focus text-success-main',
            'form_submission' => 'bg-info-focus text-info-main',
            'manual' => 'bg-neutral-200 text-secondary-light',
            default => 'bg-neutral-200 text-secondary-light',
        };
    }
}
