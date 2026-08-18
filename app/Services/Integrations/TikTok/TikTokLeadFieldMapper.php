<?php

namespace App\Services\Integrations\TikTok;

class TikTokLeadFieldMapper
{
    /**
     * Normalize TikTok webhook `changes[]` (field/value objects) or stored associative field maps.
     *
     * @param  mixed  $source
     * @return array<string, string>
     */
    public function normalizeFieldData(mixed $source): array
    {
        $fields = [];

        if (is_array($source) && array_is_list($source)) {
            foreach ($source as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $key = isset($row['field']) ? trim((string) $row['field']) : '';
                if ($key === '') {
                    continue;
                }

                $fields[$key] = $this->stringifyValue($row['value'] ?? null);
            }

            return $fields;
        }

        if (is_array($source)) {
            foreach ($source as $key => $value) {
                if (! is_string($key) || $key === '') {
                    continue;
                }

                $fields[$key] = $this->stringifyValue($value);
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, string>  $fields
     * @param  array<string, mixed>|null  $mapping
     * @return array<string, string>
     */
    public function mapToLeadAttributes(array $fields, ?array $mapping): array
    {
        $attributes = [];
        $usedTikTokFields = [];

        foreach ($mapping ?? [] as $tiktokField => $crmField) {
            if (! is_string($tiktokField) || $tiktokField === '' || ! is_string($crmField) || $crmField === '') {
                continue;
            }

            if (! TikTokCrmFields::isAllowed($crmField)) {
                continue;
            }

            $value = trim((string) ($fields[$tiktokField] ?? ''));
            if ($value === '') {
                continue;
            }

            $attributes[$crmField] = $value;
            $usedTikTokFields[] = $tiktokField;
        }

        $this->applyNameFallback($attributes, $fields);

        $custom = [];
        foreach ($fields as $tiktokField => $value) {
            if ($value === '' || in_array($tiktokField, $usedTikTokFields, true)) {
                continue;
            }

            $custom[$tiktokField] = $value;
        }

        if ($custom !== []) {
            $existing = $attributes['lead_description'] ?? '';
            $customText = collect($custom)
                ->map(fn (string $value, string $key) => $key.': '.$value)
                ->implode("\n");
            $attributes['lead_description'] = trim($existing === '' ? $customText : $existing."\n".$customText);
        }

        return $attributes;
    }

    /**
     * @param  array<string, string>  $attributes
     * @param  array<string, string>  $fields
     */
    private function applyNameFallback(array &$attributes, array $fields): void
    {
        if (($attributes['first_name'] ?? '') !== '') {
            return;
        }

        $fullName = trim((string) ($fields['name'] ?? ''));
        if ($fullName === '') {
            return;
        }

        $parts = preg_split('/\s+/', $fullName) ?: [];
        $first = array_shift($parts) ?: '';
        $last = implode(' ', $parts);

        if ($first !== '') {
            $attributes['first_name'] = $first;
        }

        if ($last !== '' && ($attributes['last_name'] ?? '') === '') {
            $attributes['last_name'] = $last;
        }
    }

    private function stringifyValue(mixed $value): string
    {
        if (is_string($value) || is_numeric($value)) {
            return trim((string) $value);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE);

            return is_string($encoded) ? $encoded : '';
        }

        return '';
    }
}
