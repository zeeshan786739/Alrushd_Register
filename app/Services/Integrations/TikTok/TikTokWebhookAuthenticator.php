<?php

namespace App\Services\Integrations\TikTok;

use Illuminate\Http\Request;

class TikTokWebhookAuthenticator
{
    /**
     * Official TikTok Business lead webhooks are signed with HMAC-SHA256.
     *
     * Header: X-Open-Signature
     * Key: App Secret
     * Message: escaped-unicode payload with lowercase hex digits
     *   (official example: äöå → \u00e4\u00f6\u00e5)
     *
     * TikTok documents that hashing decoded/raw unicode bytes produces a
     * different, invalid signature. Only the escaped representation is accepted.
     *
     * @see https://business-api.tiktok.com/portal/docs/obtain-leads-as-advertisers/v1.3
     */
    public function isValid(Request $request): bool
    {
        $secret = (string) config('integrations.tiktok.app_secret');
        $provided = $request->header('X-Open-Signature', '');

        if ($secret === '' || ! is_string($provided) || $provided === '') {
            return false;
        }

        $raw = $request->getContent();
        if ($raw === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $this->unicodeEscape($raw), $secret);

        return hash_equals($expected, $provided);
    }

    /**
     * TikTok signs an escaped-unicode version of the payload with lowercase hex digits.
     */
    public function unicodeEscape(string $payload): string
    {
        $escaped = '';
        $length = mb_strlen($payload, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($payload, $i, 1, 'UTF-8');
            $codepoint = mb_ord($char, 'UTF-8');

            if ($codepoint === false || $codepoint < 128) {
                $escaped .= $char;

                continue;
            }

            $escaped .= sprintf('\\u%04x', $codepoint);
        }

        return $escaped;
    }
}
