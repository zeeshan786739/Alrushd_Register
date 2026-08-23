<?php

namespace App\Services\Crm\LeadImport;

use App\Enums\LeadStatus;
use App\Support\LeadImportFields;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class LeadImportValueNormalizer
{
    /**
     * @param  array<int, array{key: string, label: string, index: int}>  $headers
     * @param  array<string, string>  $mapping
     * @param  array<string, mixed>  $rawValues
     * @param  array<string, mixed>  $options
     * @return array{fields: array<string, mixed>, custom: array<string, mixed>, warnings: array<int, string>}
     */
    public function normalize(array $headers, array $mapping, array $rawValues, array $options = []): array
    {
        $fields = [];
        $custom = [];
        $warnings = [];
        $labels = [];
        foreach ($headers as $header) {
            $labels[$header['key']] = $header['label'];
        }

        foreach ($headers as $header) {
            $key = $header['key'];
            $field = $mapping[$key] ?? LeadImportFields::CUSTOM;
            $raw = $rawValues[$key] ?? null;
            if ($field === LeadImportFields::IGNORE) {
                continue;
            }
            if ($field === LeadImportFields::CUSTOM) {
                if ($this->hasContent($raw)) {
                    $custom[$header['label']] = $this->stringify($raw);
                }
                continue;
            }

            [$value, $fieldWarnings] = $this->normalizeField($field, $raw, $options);
            $fields[$field] = $value;
            foreach ($fieldWarnings as $warning) {
                $warnings[] = $header['label'].': '.$warning;
            }
        }

        $this->applyNameFallback($fields, $warnings);
        $this->mergeSourceDateTime($fields, $warnings);

        return [
            'fields' => $fields,
            'custom' => $custom,
            'warnings' => $warnings,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{0: mixed, 1: array<int, string>}
     */
    private function normalizeField(string $field, mixed $raw, array $options): array
    {
        return match ($field) {
            'email' => $this->normalizeEmail($raw),
            'phone' => $this->normalizePhone($raw, $options),
            'full_name', 'first_name', 'last_name', 'company', 'city', 'province', 'postal_code', 'address',
            'campaign_name', 'adset_name', 'ad_name', 'form_name', 'notes', 'assigned_to_name', 'assigned_to_email',
            'lead_description', 'selected_school' => [$this->preserveText($raw), []],
            'advertising_platform' => $this->normalizePlatform($raw),
            'lead_status' => $this->normalizeStatus($raw, $options),
            'source_submitted_at', 'follow_up_date' => $this->normalizeDate($raw, $options),
            'source_time', 'follow_up_time' => $this->normalizeTime($raw),
            default => [$this->stringify($raw), []],
        };
    }

    /** @return array{0: ?string, 1: array<int, string>} */
    private function normalizeEmail(mixed $raw): array
    {
        $original = trim($this->stringify($raw));
        if ($original === '') {
            return [null, []];
        }
        $comparison = mb_strtolower($original);
        if (! filter_var($comparison, FILTER_VALIDATE_EMAIL)) {
            return [$original, ['Email looks invalid and was kept as a warning']];
        }

        return [$original, []];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{0: ?string, 1: array<int, string>}
     */
    private function normalizePhone(mixed $raw, array $options): array
    {
        if ($raw === null || $raw === '') {
            return [null, []];
        }

        if (is_float($raw) || is_int($raw)) {
            if (is_float($raw) && (abs($raw) >= 1e11 || str_contains(strtolower((string) $raw), 'e'))) {
                $raw = number_format($raw, 0, '', '');
            } else {
                $raw = abs($raw - round((float) $raw)) < 0.0000001
                    ? (string) (int) round((float) $raw)
                    : (string) $raw;
            }
        }

        $original = trim((string) $raw);
        if ($original === '') {
            return [null, []];
        }

        $digits = preg_replace('/\D+/', '', $original) ?? '';
        $defaultCode = preg_replace('/\D+/', '', (string) ($options['default_calling_code'] ?? '')) ?: null;
        $warnings = [];

        if ($defaultCode && $digits !== '' && ! str_starts_with($original, '+') && ! str_starts_with($digits, $defaultCode) && strlen($digits) <= 10) {
            $warnings[] = 'Phone has no country code; original value was preserved';
        }

        if ($digits === '') {
            return [$original, ['Phone could not be normalized']];
        }

        return [$original, $warnings];
    }

    /** @return array{0: ?string, 1: array<int, string>} */
    private function normalizePlatform(mixed $raw): array
    {
        $value = mb_strtolower(trim($this->stringify($raw)));
        if ($value === '') {
            return [null, []];
        }

        $map = [
            'fb' => 'facebook',
            'facebook' => 'facebook',
            'meta' => 'facebook',
            'ig' => 'instagram',
            'instagram' => 'instagram',
            'tt' => 'tiktok',
            'tiktok' => 'tiktok',
            'web' => 'web',
            'website' => 'web',
            'google' => 'google',
        ];

        return [$map[$value] ?? $value, []];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{0: string, 1: array<int, string>}
     */
    private function normalizeStatus(mixed $raw, array $options): array
    {
        $default = (string) ($options['default_status'] ?? LeadStatus::New->value);
        $value = mb_strtolower(trim($this->stringify($raw)));
        $value = str_replace([' ', '-'], '_', $value);
        if ($value === '') {
            return [$default, []];
        }

        foreach (LeadStatus::cases() as $case) {
            if ($value === $case->value || $value === mb_strtolower($case->label())) {
                return [$case->value, []];
            }
        }

        return [$default, ['Unknown status "'.$this->stringify($raw).'" defaulted to '.$default]];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{0: ?Carbon, 1: array<int, string>}
     */
    private function normalizeDate(mixed $raw, array $options): array
    {
        if (! $this->hasContent($raw)) {
            return [null, []];
        }

        if (is_numeric($raw) && (float) $raw > 20000 && (float) $raw < 80000) {
            try {
                return [Carbon::instance(ExcelDate::excelToDateTimeObject((float) $raw)), []];
            } catch (\Throwable) {
                return [null, ['Could not parse Excel serial date']];
            }
        }

        $text = trim($this->stringify($raw));
        $preferred = $options['date_format'] ?? null;
        $formats = array_filter([
            $preferred,
            'Y-m-d',
            'Y-m-d H:i:s',
            'd/m/Y',
            'd-m-Y',
            'd/m/Y H:i',
            'm/d/Y',
            'j M Y',
            'M j, Y',
        ]);

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat((string) $format, $text);
                if ($parsed !== false) {
                    return [$parsed, []];
                }
            } catch (\Throwable) {
                // try next
            }
        }

        try {
            return [Carbon::parse($text), []];
        } catch (\Throwable) {
            return [null, ['Could not parse date "'.$text.'"']];
        }
    }

    /** @return array{0: ?string, 1: array<int, string>} */
    private function normalizeTime(mixed $raw): array
    {
        if (! $this->hasContent($raw)) {
            return [null, []];
        }
        if (is_numeric($raw) && (float) $raw < 1) {
            try {
                return [Carbon::instance(ExcelDate::excelToDateTimeObject((float) $raw))->format('H:i:s'), []];
            } catch (\Throwable) {
                return [null, ['Could not parse Excel serial time']];
            }
        }

        $text = trim($this->stringify($raw));
        try {
            return [Carbon::parse($text)->format('H:i:s'), []];
        } catch (\Throwable) {
            return [$text, ['Time could not be normalized']];
        }
    }

    /**
     * @param  array<string, mixed>  $fields
     * @param  array<int, string>  $warnings
     */
    private function applyNameFallback(array &$fields, array &$warnings): void
    {
        $full = trim((string) ($fields['full_name'] ?? ''));
        if ($full !== '' && empty($fields['first_name']) && empty($fields['last_name'])) {
            $parts = preg_split('/\s+/u', $full) ?: [];
            $fields['first_name'] = $parts[0] ?? 'Unknown';
            $fields['last_name'] = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;
        }

        if (empty($fields['first_name']) && $full === '') {
            $fields['first_name'] = 'Unknown';
            $warnings[] = 'No usable name was found; first name was set to Unknown';
        }
    }

    /**
     * @param  array<string, mixed>  $fields
     * @param  array<int, string>  $warnings
     */
    private function mergeSourceDateTime(array &$fields, array &$warnings): void
    {
        $date = $fields['source_submitted_at'] ?? null;
        $time = $fields['source_time'] ?? null;
        if ($date instanceof Carbon && is_string($time) && $time !== '') {
            try {
                [$h, $m, $s] = array_pad(explode(':', $time), 3, 0);
                $date->setTime((int) $h, (int) $m, (int) $s);
                $fields['source_submitted_at'] = $date;
            } catch (\Throwable) {
                $warnings[] = 'Source time could not be merged into the source date';
            }
        }
        unset($fields['source_time']);
    }

    private function preserveText(mixed $raw): ?string
    {
        if (! $this->hasContent($raw)) {
            return null;
        }

        return trim($this->stringify($raw));
    }

    private function stringify(mixed $raw): string
    {
        if ($raw === null) {
            return '';
        }
        if (is_bool($raw)) {
            return $raw ? 'true' : 'false';
        }
        if (is_float($raw) || is_int($raw)) {
            if (is_float($raw) && abs($raw - round($raw)) < 0.0000001) {
                return (string) (int) round($raw);
            }

            return (string) $raw;
        }

        return (string) $raw;
    }

    private function hasContent(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }
}
