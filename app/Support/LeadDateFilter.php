<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Date range filter for the Leads page: ?date_field=created&date_from=2026-11-01&date_to=2026-11-06.
 * "Enquiry date" exists because imported leads were all added on the day of the import,
 * so only their original date says when the family actually got in touch.
 */
final class LeadDateFilter
{
    public const FIELDS = [
        'created' => 'Date added',
        'enquiry' => 'Enquiry date',
        'follow_up' => 'Follow-up date',
    ];

    private const DEFAULT_FIELD = 'created';

    public static function field(Request $request): string
    {
        $field = $request->query('date_field');

        return is_string($field) && array_key_exists($field, self::FIELDS) ? $field : self::DEFAULT_FIELD;
    }

    /** @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable} Invalid dates count as not set; a reversed range is put right. */
    public static function range(Request $request): array
    {
        $from = self::parse($request->query('date_from'));
        $to = self::parse($request->query('date_to'));

        if ($from && $to && $from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    public static function apply($query, Request $request): void
    {
        [$from, $to] = self::range($request);
        if (! $from && ! $to) {
            return;
        }

        $field = self::field($request);

        if ($field === 'follow_up') {
            $query->when($from, fn ($q) => $q->where('next_follow_up_date', '>=', $from->toDateString()))
                ->when($to, fn ($q) => $q->where('next_follow_up_date', '<=', $to->toDateString()));

            return;
        }

        $column = $field === 'enquiry' ? 'COALESCE(source_submitted_at, created_at)' : 'created_at';

        $query->when($from, fn ($q) => $q->whereRaw($column.' >= ?', [$from->startOfDay()->toDateTimeString()]))
            ->when($to, fn ($q) => $q->whereRaw($column.' <= ?', [$to->endOfDay()->toDateTimeString()]));
    }

    /** Short text for the picker button, such as "1–6 Nov 2026". */
    public static function summary(Request $request): ?string
    {
        [$from, $to] = self::range($request);

        if ($from && $to) {
            if ($from->isSameDay($to)) {
                return $from->format('j M Y');
            }
            if ($from->isSameMonth($to)) {
                return $from->format('j').'–'.$to->format('j M Y');
            }

            return $from->isSameYear($to)
                ? $from->format('j M').' – '.$to->format('j M Y')
                : $from->format('j M Y').' – '.$to->format('j M Y');
        }
        if ($from) {
            return 'From '.$from->format('j M Y');
        }

        return $to ? 'Until '.$to->format('j M Y') : null;
    }

    private static function parse(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value ? $date : null;
    }
}
