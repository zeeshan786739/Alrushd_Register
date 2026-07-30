<?php

namespace App\Services\Integrations\Meta;

use Illuminate\Http\Client\Response;
use RuntimeException;

class MetaGraphException extends RuntimeException
{
    public static function fromResponse(Response $response): self
    {
        $body = $response->json();
        $message = (string) ($body['error']['message'] ?? trim($response->body()));
        $code = $body['error']['code'] ?? $response->status();
        $type = $body['error']['type'] ?? 'GraphApiException';

        return new self("Meta API ({$type} #{$code}): {$message}");
    }
}
