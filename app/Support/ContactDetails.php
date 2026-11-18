<?php

namespace App\Support;

/**
 * Website CMS → Contact details: builds the two values the website reads from structured
 * settings — the opening-hours text ("Mon–Fri, 8:30am–4:30pm (UK)") and the WhatsApp link
 * ("https://wa.me/442036330757?text=…") — and reads existing text back into those settings.
 *
 * Reading back only succeeds when rebuilding gives exactly the same text, so an older value
 * is never reworded on the live website; otherwise it is kept as it is until edited.
 * The CMS editor (website-cms-contact.js) mirrors hoursText() and whatsappLink().
 */
class ContactDetails
{
    public const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    public const TIMEZONES = ['UK', 'Ireland', 'Pakistan', 'UAE', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Egypt', 'Malaysia', 'Nigeria'];

    /** Mon–Fri open, used when nothing has been set (or the old text can't be read). */
    public const DEFAULT_DAYS = [true, true, true, true, true, false, false];

    /** "Mon–Fri", "Sat, Sun", "Mon–Wed, Fri", "Closed". Runs of 3+ days become ranges. */
    public static function dayList(array $days): string
    {
        $open = array_values(array_map(fn ($d) => (bool) $d, array_pad(array_slice($days, 0, 7), 7, false)));
        $parts = [];
        for ($i = 0; $i < 7; $i++) {
            if (! $open[$i]) {
                continue;
            }
            $j = $i;
            while ($j + 1 < 7 && $open[$j + 1]) {
                $j++;
            }
            $run = $j - $i + 1;
            if ($run >= 3) {
                $parts[] = self::DAYS[$i].'–'.self::DAYS[$j];
            } else {
                for ($k = $i; $k <= $j; $k++) {
                    $parts[] = self::DAYS[$k];
                }
            }
            $i = $j;
        }

        return $parts ? implode(', ', $parts) : 'Closed';
    }

    /** "08:30" → "8:30am", "16:00" → "4pm", "12:00" → "12pm". */
    public static function timeText(string $hhmm): string
    {
        if (! preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $hhmm, $m)) {
            return '';
        }
        $h = (int) $m[1];
        $suffix = $h >= 12 ? 'pm' : 'am';
        $h12 = $h % 12 ?: 12;

        return $h12.($m[2] === '00' ? '' : ':'.$m[2]).$suffix;
    }

    /** The text the website shows, e.g. "Mon–Fri, 8:30am–4:30pm (UK)", or "Closed". */
    public static function hoursText(array $days, string $from, string $until, string $timezone): string
    {
        $list = self::dayList($days);
        if ($list === 'Closed') {
            return 'Closed';
        }

        return sprintf('%s, %s–%s (%s)', $list, self::timeText($from), self::timeText($until), $timezone);
    }

    /**
     * @return array{opening_days: list<bool>, opening_from: string, opening_until: string, opening_timezone: string}|null
     */
    public static function parseHours(string $text): ?array
    {
        $text = trim($text);
        if ($text === 'Closed') {
            return ['opening_days' => array_fill(0, 7, false), 'opening_from' => '08:30', 'opening_until' => '16:30', 'opening_timezone' => 'UK'];
        }
        if (! preg_match('/^(?<days>.+?),\s*(?<from>\d{1,2}(?::\d{2})?[ap]m)–(?<until>\d{1,2}(?::\d{2})?[ap]m)\s*\((?<tz>[^)]+)\)$/u', $text, $m)) {
            return null;
        }
        if (! in_array($m['tz'], self::TIMEZONES, true)) {
            return null;
        }

        $days = array_fill(0, 7, false);
        foreach (explode(', ', $m['days']) as $token) {
            $range = explode('–', $token);
            $a = array_search($range[0], self::DAYS, true);
            $b = array_search($range[1] ?? $range[0], self::DAYS, true);
            if ($a === false || $b === false || $b < $a || count($range) > 2) {
                return null;
            }
            for ($i = $a; $i <= $b; $i++) {
                $days[$i] = true;
            }
        }

        $parsed = [
            'opening_days' => $days,
            'opening_from' => self::to24($m['from']),
            'opening_until' => self::to24($m['until']),
            'opening_timezone' => $m['tz'],
        ];

        // Only accept text we would write ourselves, so the live wording never changes.
        return self::hoursText($days, $parsed['opening_from'], $parsed['opening_until'], $m['tz']) === $text ? $parsed : null;
    }

    private static function to24(string $time): string
    {
        preg_match('/^(\d{1,2})(?::(\d{2}))?([ap]m)$/', $time, $m);
        $h = ((int) $m[1]) % 12 + ($m[3] === 'pm' ? 12 : 0);

        return sprintf('%02d:%s', $h, $m[2] !== '' ? $m[2] : '00');
    }

    public static function digits(string $number): string
    {
        return preg_replace('/\D+/', '', $number) ?? '';
    }

    /** https://wa.me/{digits}[?text={message}] — '' when there is no number. */
    public static function whatsappLink(string $number, string $message = ''): string
    {
        $digits = self::digits($number);
        if ($digits === '') {
            return '';
        }
        $message = trim($message);

        return 'https://wa.me/'.$digits.($message !== '' ? '?text='.rawurlencode($message) : '');
    }

    /**
     * @return array{whatsapp_same_as_phone: bool, whatsapp_number: string, whatsapp_message: string}|null
     */
    public static function parseWhatsapp(string $link, string $phone): ?array
    {
        if (! preg_match('#^https://wa\.me/(\d+)(?:\?text=([^&]*))?$#', trim($link), $m)) {
            return null;
        }
        $message = isset($m[2]) ? rawurldecode($m[2]) : '';
        $same = $m[1] === self::digits($phone);
        $parsed = [
            'whatsapp_same_as_phone' => $same,
            'whatsapp_number' => $same ? '' : '+'.$m[1],
            'whatsapp_message' => $message,
        ];

        return self::whatsappLink($m[1], $message) === trim($link) ? $parsed : null;
    }
}
