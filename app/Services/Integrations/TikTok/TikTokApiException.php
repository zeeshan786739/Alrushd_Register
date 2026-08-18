<?php

namespace App\Services\Integrations\TikTok;

use Illuminate\Http\Client\Response;
use RuntimeException;

class TikTokApiException extends RuntimeException
{
    public static function fromHttpFailure(Response $response): self
    {
        $status = $response->status();

        return new self("TikTok API request failed (HTTP {$status}).");
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromApiPayload(array $payload): self
    {
        $code = $payload['code'] ?? 'unknown';
        $message = self::safeMessage($payload['message'] ?? null);

        return new self("TikTok API error ({$code}): {$message}");
    }

    public static function invalidResponse(): self
    {
        return new self('TikTok API returned an unexpected response.');
    }

    public static function missingAccessToken(): self
    {
        return new self('TikTok did not return an access token.');
    }

    public static function noAdvertisers(): self
    {
        return new self('No TikTok Ads Manager accounts were authorized for this app.');
    }

    public function userMessage(): string
    {
        return 'TikTok could not complete authorization. Please try connecting again.';
    }

    public function operationUserMessage(): string
    {
        return 'TikTok could not complete this request. Please try again.';
    }

    private static function safeMessage(mixed $message): string
    {
        if (! is_string($message) || $message === '') {
            return 'Authorization failed.';
        }

        $redacted = preg_replace('/(access_token|secret|auth_code|app_secret)\s*[:=]\s*\S+/i', '$1=[redacted]', $message);

        return is_string($redacted) && $redacted !== '' ? $redacted : 'Authorization failed.';
    }
}
