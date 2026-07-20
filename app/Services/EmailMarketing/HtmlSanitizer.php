<?php

namespace App\Services\EmailMarketing;

class HtmlSanitizer
{
    public function sanitize(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><a><span><div><h1><h2><h3><h4><table><thead><tbody><tr><td><th><img>';
        $clean = strip_tags($html, $allowed);

        // Remove javascript: and on* handlers from remaining tags.
        $clean = preg_replace('/\son\w+\s*=\s*("|\')[^"\']*\1/i', '', $clean) ?? $clean;
        $clean = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*\2/i', '$1="#"', $clean) ?? $clean;

        return $clean;
    }

    public function toPlainText(?string $html): string
    {
        return trim(html_entity_decode(strip_tags((string) $html)));
    }
}
