<?php

namespace App\Support;

use App\Models\Form;
use App\Models\FormEntry;

/**
 * Search, filter and sort form submissions by the answers stored in their JSON
 * `data` column. Forms name their fields differently (position_applied_for on
 * job applications, job_applied_for on staff applications), so each concept
 * lists the keys that can hold it, and the first one with a value wins.
 */
final class FormEntryBrowse
{
    public const DEFAULT_SORT = 'newest';

    private const MAX_SEARCH_LENGTH = 200;

    private const FULL_NAME_KEYS = ['full_name', 'fullname', 'name', 'student_name', 'applicant_name', 'parent_name'];

    private const FIRST_NAME_KEYS = ['first_name', 'fname'];

    private const LAST_NAME_KEYS = ['last_name', 'lname'];

    private const EMAIL_KEYS = ['email', 'email_address', 'parent_email'];

    private const PHONE_KEYS = ['phone', 'mobile', 'mobile_number', 'telephone', 'contact_number'];

    private const FACETS = [
        'role' => [
            'label' => 'Role applied for',
            'keys' => ['position_applied_for', 'job_applied_for', 'role_applied_for', 'role', 'position'],
            'label_pattern' => '/appl(y|ied|ying)|position|vacancy|\brole\b/i',
            'match' => 'exact',
        ],
        'subject' => [
            'label' => 'Subject',
            'keys' => ['subject_skill', 'subject_applied_for', 'subject', 'subjects'],
            'label_pattern' => '/\b(subject|course)/i',
            'match' => 'contains',
        ],
        'year_group' => [
            'label' => 'Year group',
            'keys' => ['year_group', 'student_year'],
            'label_pattern' => null,
            'match' => 'exact',
        ],
    ];

    /**
     * Facets that apply to this form, with the form's own wording as the label.
     * Without a form, every known key is searched (the all-forms CRM list).
     *
     * @return array<string, array{label: string, keys: list<string>, match: string}>
     */
    public static function facets(?Form $form = null): array
    {
        $facets = [];
        $fields = $form?->fields ?? collect();

        foreach (self::FACETS as $name => $facet) {
            $formField = $fields->first(fn ($field) => in_array($field->key, $facet['keys'], true));

            if (! $formField && $facet['label_pattern']) {
                $formField = $fields->first(fn ($field) => self::isSafeKey((string) $field->key)
                    && in_array($field->type, ['text', 'select', 'radio'], true)
                    && preg_match($facet['label_pattern'], (string) $field->label));
            }

            $keys = $facet['keys'];
            if ($formField && ! in_array($formField->key, $keys, true)) {
                array_unshift($keys, $formField->key);
            }

            $facets[$name] = [
                'label' => $formField?->label ?: $facet['label'],
                'keys' => $keys,
                'match' => $facet['match'],
            ];
        }

        return $facets;
    }

    /**
     * Distinct answers for a facet, de-duplicated regardless of case and spacing.
     *
     * @return array<string, string> value => label
     */
    public static function facetOptions($query, array $facet, int $limit = 200): array
    {
        $expression = self::valueExpression($facet['keys']);

        $values = (clone $query)
            ->reorder()
            ->selectRaw($expression.' as facet_value')
            ->whereRaw($expression.' IS NOT NULL')
            ->distinct()
            ->limit($limit)
            ->pluck('facet_value');

        return $values
            ->map(fn ($value) => trim(preg_replace('/\s+/', ' ', (string) $value) ?? ''))
            ->filter(fn ($value) => $value !== '' && strlen($value) <= 120)
            ->unique(fn ($value) => mb_strtolower($value))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->mapWithKeys(fn ($value) => [$value => $value])
            ->all();
    }

    /** @return array<string, string> sort key => label */
    public static function sortOptions(array $facets, array $availableFacets): array
    {
        $options = [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
            'name_asc' => 'Name (A–Z)',
            'name_desc' => 'Name (Z–A)',
        ];

        foreach (['role', 'subject', 'year_group'] as $name) {
            if (isset($availableFacets[$name])) {
                $label = $facets[$name]['label'];
                $options[$name.'_asc'] = $label.' (A–Z)';
                $options[$name.'_desc'] = $label.' (Z–A)';
            }
        }

        return $options + ['status' => 'Status'];
    }

    public static function applySearch($query, ?string $search): void
    {
        $search = mb_substr(trim((string) $search), 0, self::MAX_SEARCH_LENGTH);
        if ($search === '') {
            return;
        }

        $needle = '%'.addcslashes(mb_strtolower($search), '\\%_').'%';
        $digits = preg_replace('/\D+/', '', $search) ?? '';

        $query->where(function ($inner) use ($search, $needle, $digits) {
            // JSON columns compare case-sensitively, so lower-case both sides.
            // JSON_SEARCH looks at answers only, so "name" does not match every row by its key.
            $inner->whereRaw("JSON_SEARCH(LOWER(CAST(form_entries.data AS CHAR)), 'one', ?) IS NOT NULL", [$needle])
                ->orWhereRaw('LOWER('.self::nameExpression().') LIKE ?', [$needle]);

            if (strlen($digits) >= 4) {
                $phone = self::valueExpression(self::PHONE_KEYS);
                $inner->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE({$phone}, ' ', ''), '-', ''), '+', ''), '(', '') LIKE ?", ['%'.$digits.'%']);
            }

            if (ctype_digit($search)) {
                $inner->orWhere('form_entries.id', (int) $search)->orWhere('form_entries.entry_id', (int) $search);
            }
        });
    }

    public static function applyFacet($query, array $facet, ?string $value): void
    {
        $value = trim((string) $value);
        if ($value === '') {
            return;
        }

        $expression = 'LOWER('.self::valueExpression($facet['keys']).')';
        $normalized = mb_strtolower($value);

        if ($facet['match'] === 'contains') {
            $query->whereRaw($expression.' LIKE ?', ['%'.addcslashes($normalized, '\\%_').'%']);

            return;
        }

        $query->whereRaw($expression.' = ?', [$normalized]);
    }

    public static function normalizeSort(?string $sort, array $options): string
    {
        return is_string($sort) && array_key_exists($sort, $options) ? $sort : self::DEFAULT_SORT;
    }

    /** Applies the sort; blank answers always go last, then newest first as a stable tie-break. */
    public static function applySort($query, string $sort, array $facets): void
    {
        $query->reorder();

        if ($sort === 'oldest') {
            $query->orderBy('form_entries.submitted_at')->orderBy('form_entries.id');

            return;
        }

        if ($sort === 'status') {
            $query->orderByRaw("FIELD(form_entries.status, 'pending', 'approved', 'rejected')");
        } elseif (preg_match('/^(name|role|subject|year_group)_(asc|desc)$/', $sort, $match)) {
            $expression = $match[1] === 'name'
                ? self::nameExpression()
                : (isset($facets[$match[1]]) ? self::valueExpression($facets[$match[1]]['keys']) : null);

            if ($expression !== null) {
                $direction = $match[2] === 'desc' ? 'DESC' : 'ASC';
                $query->orderByRaw("{$expression} IS NULL")->orderByRaw("LOWER({$expression}) {$direction}");
            }
        }

        $query->orderByDesc('form_entries.submitted_at')->orderByDesc('form_entries.id');
    }

    /**
     * What a results row shows for one submission.
     *
     * @return array{name: ?string, email: ?string, phone: ?string, facets: array<string, string>, preview: ?string}
     */
    public static function summary(FormEntry $entry, array $facets): array
    {
        $data = is_array($entry->data) ? $entry->data : (json_decode((string) $entry->data, true) ?? []);
        $fields = is_array($data['fields'] ?? null) ? $data['fields'] : $data;

        $name = self::pick($fields, self::FULL_NAME_KEYS);
        if ($name === null) {
            $name = trim((self::pick($fields, self::FIRST_NAME_KEYS) ?? '').' '.(self::pick($fields, self::LAST_NAME_KEYS) ?? '')) ?: null;
        }

        $values = [];
        foreach ($facets as $facetName => $facet) {
            if (($value = self::pick($fields, $facet['keys'])) !== null) {
                $values[$facetName] = $value;
            }
        }

        $identity = array_merge(self::FULL_NAME_KEYS, self::FIRST_NAME_KEYS, self::LAST_NAME_KEYS, self::EMAIL_KEYS, self::PHONE_KEYS);
        $preview = [];
        foreach ($fields as $key => $value) {
            if (is_scalar($value) && ! in_array($key, $identity, true) && trim((string) $value) !== '' && mb_strlen((string) $value) < 80) {
                $preview[] = ucfirst(str_replace('_', ' ', (string) $key)).': '.$value;
            }
            if (count($preview) === 2) {
                break;
            }
        }

        return [
            'name' => $name,
            'email' => self::pick($fields, self::EMAIL_KEYS),
            'phone' => self::pick($fields, self::PHONE_KEYS),
            'facets' => $values,
            'preview' => $preview !== [] ? implode(' · ', $preview) : null,
        ];
    }

    /** @param  array<string, mixed>  $fields */
    private static function pick(array $fields, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $fields[$key] ?? null;
            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    private static function isSafeKey(string $key): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_]+$/', $key);
    }

    /** SQL for the first non-blank answer among the given keys, at the top level or under "fields". */
    private static function valueExpression(array $keys): string
    {
        $parts = [];
        foreach ($keys as $key) {
            if (! self::isSafeKey((string) $key)) {
                continue;
            }
            foreach (["$.\"{$key}\"", "$.fields.\"{$key}\""] as $path) {
                // An explicit JSON null unquotes to the string 'null'.
                $parts[] = "NULLIF(NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(form_entries.data, '{$path}'))), ''), 'null')";
            }
        }

        return $parts === [] ? 'NULL' : 'COALESCE('.implode(', ', $parts).')';
    }

    private static function nameExpression(): string
    {
        $full = self::valueExpression(self::FULL_NAME_KEYS);
        $first = self::valueExpression(self::FIRST_NAME_KEYS);
        $last = self::valueExpression(self::LAST_NAME_KEYS);

        return "COALESCE({$full}, NULLIF(CONCAT_WS(' ', {$first}, {$last}), ''))";
    }
}
