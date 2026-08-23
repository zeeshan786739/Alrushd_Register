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

        $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><a><span><div><h1><h2><h3><h4><table><thead><tbody><tr><td><th><img>';
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

        $clean = preg_replace('/\sstyle\s*=\s*("|\')[^"\']*\1/i', '', $clean) ?? $clean;

        return $clean;
    }

    public function toPlainText(?string $html): string
    {
        return trim(html_entity_decode(strip_tags((string) $html)));
    }
}
