<?php

namespace App\Support;

/**
 * Office-style color palette for custom CRM lead statuses.
 */
final class CrmColorPalette
{
    /** @return list<string> hex presets (top row circles) */
    public static function presets(): array
    {
        return [
            '#000000', '#FFFFFF', '#FF0000', '#00FF00', '#0000FF', '#FFFF00',
            '#00FFFF', '#FF00FF', '#C0C0C0', '#808080', '#800000', '#808000',
            '#008000', '#800080', '#008080', '#000080',
        ];
    }

    /**
     * Large circular swatches for the status color picker UI.
     *
     * @return list<string>
     */
    public static function circles(): array
    {
        return [
            '#EF4444', '#F97316', '#F59E0B', '#EAB308', '#84CC16', '#22C55E',
            '#14B8A6', '#06B6D4', '#0EA5E9', '#0080FF', '#3B82F6', '#6366F1',
            '#8B5CF6', '#A855F7', '#D946EF', '#EC4899', '#F43F5E', '#78716C',
            '#64748B', '#334155', '#111827', '#FFFFFF',
        ];
    }

    /**
     * 12×10 theme grid (columns = hue families, rows = light → dark).
     *
     * @return list<list<string>>
     */
    public static function grid(): array
    {
        return [
            // Row 1 — light pastels
            ['#FFCCCC', '#FFE5CC', '#FFFFCC', '#E5FFCC', '#CCFFCC', '#CCFFE5', '#CCFFFF', '#CCE5FF', '#CCCCFF', '#E5CCFF', '#FFCCFF', '#FFFFFF'],
            // Row 2
            ['#FF9999', '#FFCC99', '#FFFF99', '#CCFF99', '#99FF99', '#99FFCC', '#99FFFF', '#99CCFF', '#9999FF', '#CC99FF', '#FF99FF', '#F2F2F2'],
            // Row 3
            ['#FF6666', '#FFB366', '#FFFF66', '#B3FF66', '#66FF66', '#66FFB3', '#66FFFF', '#66B3FF', '#6666FF', '#B366FF', '#FF66FF', '#D9D9D9'],
            // Row 4
            ['#FF3333', '#FF9933', '#FFFF33', '#99FF33', '#33FF33', '#33FF99', '#33FFFF', '#3399FF', '#3333FF', '#9933FF', '#FF33FF', '#BFBFBF'],
            // Row 5 — saturated mid
            ['#FF0000', '#FF8000', '#FFFF00', '#80FF00', '#00FF00', '#00FF80', '#00FFFF', '#0080FF', '#0000FF', '#8000FF', '#FF00FF', '#A6A6A6'],
            // Row 6
            ['#CC0000', '#CC6600', '#CCCC00', '#66CC00', '#00CC00', '#00CC66', '#00CCCC', '#0066CC', '#0000CC', '#6600CC', '#CC00CC', '#808080'],
            // Row 7
            ['#990000', '#994C00', '#999900', '#4C9900', '#009900', '#00994C', '#009999', '#004C99', '#000099', '#4C0099', '#990099', '#595959'],
            // Row 8
            ['#660000', '#663300', '#666600', '#336600', '#006600', '#006633', '#006666', '#003366', '#000066', '#330066', '#660066', '#404040'],
            // Row 9
            ['#330000', '#331A00', '#333300', '#1A3300', '#003300', '#00331A', '#003333', '#001A33', '#000033', '#1A0033', '#330033', '#262626'],
            // Row 10 — near black
            ['#1A0000', '#1A0D00', '#1A1A00', '#0D1A00', '#001A00', '#001A0D', '#001A1A', '#000D1A', '#00001A', '#0D001A', '#1A001A', '#000000'],
        ];
    }

    /** @return list<string> flat list of all selectable colors */
    public static function all(): array
    {
        $colors = self::presets();
        foreach (self::grid() as $row) {
            foreach ($row as $hex) {
                $colors[] = strtoupper($hex);
            }
        }

        return array_values(array_unique(array_map(static fn (string $c) => strtoupper($c), $colors)));
    }

    public static function isHex(?string $value): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $value);
    }

    public static function normalize(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (! str_starts_with($value, '#')) {
            $value = '#'.$value;
        }
        if (! self::isHex($value)) {
            return null;
        }

        return strtoupper($value);
    }

    /**
     * Soft fill / border / readable text derived from a solid hex.
     *
     * @return array{bg: string, border: string, text: string, ring: string, solid: string}
     */
    public static function softPalette(string $hex): array
    {
        $hex = self::normalize($hex) ?? '#2563EB';
        [$r, $g, $b] = self::rgb($hex);
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        // Soft background: blend toward white
        $bg = self::mix($r, $g, $b, 255, 255, 255, 0.86);
        $border = self::mix($r, $g, $b, 255, 255, 255, 0.35);
        // Dark text for readability on soft bg
        $text = $luminance > 0.72
            ? self::mix($r, $g, $b, 0, 0, 0, 0.55)
            : self::mix($r, $g, $b, 0, 0, 0, 0.35);
        $ring = sprintf('rgba(%d,%d,%d,.22)', $r, $g, $b);

        return [
            'bg' => $bg,
            'border' => $border,
            'text' => $text,
            'ring' => $ring,
            'solid' => $hex,
        ];
    }

    public static function cssVars(string $hex): string
    {
        $p = self::softPalette($hex);

        return sprintf(
            '--crm-tone-bg:%s;--crm-tone-border:%s;--crm-tone-text:%s;--crm-tone-ring:%s;--crm-tone-solid:%s',
            $p['bg'],
            $p['border'],
            $p['text'],
            $p['ring'],
            $p['solid']
        );
    }

    /** @return array{0: int, 1: int, 2: int} */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function mix(int $r1, int $g1, int $b1, int $r2, int $g2, int $b2, float $towardSecond): string
    {
        $t = max(0, min(1, $towardSecond));
        $r = (int) round($r1 + ($r2 - $r1) * $t);
        $g = (int) round($g1 + ($g2 - $g1) * $t);
        $b = (int) round($b1 + ($b2 - $b1) * $t);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
