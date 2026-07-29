<?php

namespace App\Services\Integrations\Meta;

class MetaLeadFieldMapper
{
    /** @param  array<int, array{name: string, values: array<int, string>}>  $fieldData */
    public function normalizeFieldData(array $fieldData): array
    {
        $normalized = [];

        foreach ($fieldData as $field) {
            $name = (string) ($field['name'] ?? '');
            $values = $field['values'] ?? [];
            $normalized[$name] = is_array($values) ? implode(', ', array_filter($values)) : (string) $values;
        }

        return $normalized;
    }

    /**
     * @param  array<string, string>  $fields
     * @param  array<string, string>|null  $customMapping
     * @return array<string, mixed>
     */
    public function mapToLeadAttributes(array $fields, ?array $customMapping = null): array
    {
        $mapping = $customMapping ?: $this->defaultFieldMapping();
        $attributes = [
            'first_name' => 'Unknown',
            'last_name' => null,
            'email' => null,
            'phone' => null,
            'lead_description' => null,
            'extra' => [],
        ];

        foreach ($fields as $metaKey => $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $crmKey = $mapping[$metaKey] ?? null;

            if ($crmKey === 'first_name' || $crmKey === 'last_name') {
                $attributes[$crmKey] = $value;
            } elseif ($crmKey === 'full_name') {
                [$first, $last] = $this->splitFullName($value);
                $attributes['first_name'] = $first;
                $attributes['last_name'] = $last;
            } elseif in_array($crmKey, ['email', 'phone', 'company', 'selected_school', 'city', 'postal_code'], true) {
                $attributes[$crmKey] = $value;
            } elseif ($crmKey === 'lead_description') {
                $attributes['lead_description'] = $this->appendDescription($attributes['lead_description'], $metaKey, $value);
            } elseif ($crmKey === null) {
                $attributes['extra'][$metaKey] = $value;
            } else {
                $attributes['extra'][$metaKey] = $value;
            }
        }

        if (! empty($attributes['extra'])) {
            $extraLines = [];
            foreach ($attributes['extra'] as $key => $extraValue) {
                $extraLines[] = $this->humanizeFieldName($key).': '.$extraValue;
            }
            $attributes['lead_description'] = $this->appendDescription(
                $attributes['lead_description'],
                'Additional fields',
                implode(' | ', $extraLines)
            );
        }

        unset($attributes['extra']);

        return $attributes;
    }

    /** @return array<string, string> */
    public function defaultFieldMapping(): array
    {
        return [
            'full_name' => 'full_name',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
            'phone_number' => 'phone',
            'phone' => 'phone',
            'company_name' => 'company',
            'city' => 'city',
            'zip_code' => 'postal_code',
            'post_code' => 'postal_code',
        ];
    }

    /** @return array{0: string, 1: ?string} */
    private function splitFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [
            $parts[0] ?? 'Unknown',
            $parts[1] ?? null,
        ];
    }

    private function appendDescription(?string $existing, string $label, string $value): string
    {
        $line = $this->humanizeFieldName($label).': '.$value;

        return $existing ? $existing.' | '.$line : $line;
    }

    private function humanizeFieldName(string $name): string
    {
        return ucwords(str_replace(['_', '?'], [' ', ''], $name));
    }
}
