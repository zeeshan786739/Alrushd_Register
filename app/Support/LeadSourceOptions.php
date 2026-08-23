<?php

namespace App\Support;

final class LeadSourceOptions
{
    /** @return array<string, string> */
    public static function filterOptions(): array
    {
        return [
            'facebook_lead_ads' => 'Facebook Lead Ads',
            'tiktok_lead_ads' => 'TikTok Lead Ads',
            'student_admission' => 'Student Admission',
            'form_submission' => 'Form Submission',
            'file_import' => 'File Import',
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
            'tiktok_lead_ads' => 'logos:tiktok-icon',
            'student_admission' => 'solar:square-academic-cap-linear',
            'form_submission' => 'solar:inbox-in-linear',
            'file_import' => 'solar:import-linear',
            'manual' => 'solar:pen-linear',
            default => 'solar:link-linear',
        };
    }

    public static function badgeClass(?string $source): string
    {
        return match ($source) {
            'facebook_lead_ads' => 'bg-primary-50 text-primary-600',
            'tiktok_lead_ads' => 'bg-neutral-900 text-white',
            'student_admission' => 'bg-success-focus text-success-main',
            'form_submission' => 'bg-info-focus text-info-main',
            'file_import' => 'bg-warning-focus text-warning-main',
            'manual' => 'bg-neutral-200 text-secondary-light',
            default => 'bg-neutral-200 text-secondary-light',
        };
    }
}
