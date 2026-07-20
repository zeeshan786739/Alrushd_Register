<?php

namespace App\Services\EmailMarketing;

class TemplateRenderer
{
    /**
     * Supported placeholders: {{name}}, {{email}}, {{company}}, {{unsubscribe_url}}
     *
     * @param  array<string, string|null>  $variables
     */
    public function render(string $content, array $variables): string
    {
        $map = [
            '{{name}}' => e((string) ($variables['name'] ?? '')),
            '{{email}}' => e((string) ($variables['email'] ?? '')),
            '{{company}}' => e((string) ($variables['company'] ?? '')),
            '{{unsubscribe_url}}' => e((string) ($variables['unsubscribe_url'] ?? '')),
        ];

        return str_replace(array_keys($map), array_values($map), $content);
    }

    /** @return list<string> */
    public function missingVariables(string $content, array $variables): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $content, $matches);
        $used = array_unique($matches[1] ?? []);
        $supported = ['name', 'email', 'company', 'unsubscribe_url'];
        $missing = [];

        foreach ($used as $key) {
            if (! in_array($key, $supported, true)) {
                $missing[] = $key;
            } elseif (! array_key_exists($key, $variables) || $variables[$key] === null || $variables[$key] === '') {
                if ($key !== 'company') {
                    $missing[] = $key;
                }
            }
        }

        return $missing;
    }
}
