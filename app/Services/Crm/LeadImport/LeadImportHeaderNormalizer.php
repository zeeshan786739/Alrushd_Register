<?php

namespace App\Services\Crm\LeadImport;

class LeadImportHeaderNormalizer
{
    public function normalize(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/u', '', $header) ?? $header;
        $header = trim($header);
        $header = preg_replace('/\s+/u', ' ', $header) ?? $header;
        $header = mb_strtolower($header);
        $header = str_replace(['’', "'", '`'], '', $header);
        $header = preg_replace('/[^a-z0-9]+/u', '_', $header) ?? $header;

        return trim($header, '_');
    }

    /**
     * @param  array<int, array{key: string, label: string, index: int}>  $headers
     */
    public function signature(array $headers): string
    {
        $parts = [];
        foreach ($headers as $header) {
            $parts[] = $header['index'].':'.$this->normalize($header['label']);
        }

        return hash('sha256', implode('|', $parts));
    }
}
