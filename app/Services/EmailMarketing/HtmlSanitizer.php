<?php

namespace App\Services\EmailMarketing;

/**
 * Allowlisted HTML sanitizer for email display/storage.
 * Never trust raw inbound HTML.
 */
class HtmlSanitizer
{
    public function sanitize(?string $html): string
    {
        if (! $html) {
            return '';
        }

        // Drop high-risk containers before allowlisting.
        $html = preg_replace('/<(script|iframe|object|embed|form|link|meta|base|svg|math)[^>]*>.*?<\/\1>/is', '', $html) ?? $html;
        $html = preg_replace('/<(script|iframe|object|embed|form|link|meta|base|svg|math)[^>]*\/?>/is', '', $html) ?? $html;

        $allowed = '<p><br><hr><b><strong><i><em><u><ul><ol><li><a><span><div><h1><h2><h3><h4><table><thead><tbody><tr><td><th><img>';
        $clean = strip_tags($html, $allowed);

        // Remove event handlers (quoted and unquoted).
        $clean = preg_replace('/\son\w+\s*=\s*("|\')[^"\']*\1/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\son\w+\s*=\s*[^\s>]+/i', '', $clean) ?? $clean;

        // Neutralize dangerous URL schemes in href/src/style.
        $clean = preg_replace_callback(
            '/\b(href|src)\s*=\s*("|\')(.*?)\2/i',
            static function (array $m): string {
                $attr = $m[1];
                $quote = $m[2];
                $url = trim(html_entity_decode($m[3], ENT_QUOTES | ENT_HTML5));
                if (preg_match('/^\s*(javascript|vbscript|data)\s*:/i', $url)) {
                    return $attr.'='.$quote.'#'.$quote;
                }

                return $m[0];
            },
            $clean
        ) ?? $clean;

        $safeProperties = ['color','background','background-color','font-family','font-size','font-weight','font-style','line-height','text-align','text-decoration','letter-spacing','width','max-width','height','padding','padding-top','padding-right','padding-bottom','padding-left','margin','margin-top','margin-right','margin-bottom','margin-left','border','border-top','border-right','border-bottom','border-left','border-radius','border-collapse','display','vertical-align'];
        $clean = preg_replace_callback('/\sstyle\s*=\s*("|\')(.*?)\1/is', static function (array $match) use ($safeProperties): string {
            $safe = [];
            foreach (explode(';', $match[2]) as $declaration) {
                if (! str_contains($declaration, ':')) continue;
                [$property, $value] = array_map('trim', explode(':', $declaration, 2));
                $property = strtolower($property);
                if (! in_array($property, $safeProperties, true)) continue;
                if (preg_match('/expression|javascript:|vbscript:|data:|url\s*\(/i', $value)) continue;
                if (! preg_match('/^[#(),.%\-\w\s\'"\/]+$/u', $value)) continue;
                $safe[] = $property.':'.$value;
            }
            return $safe ? ' style="'.htmlspecialchars(implode(';', $safe), ENT_QUOTES | ENT_HTML5).'"' : '';
        }, $clean) ?? $clean;

        return $clean;
    }

    public function toPlainText(?string $html): string
    {
        return trim(html_entity_decode(strip_tags((string) $html)));
    }
}
