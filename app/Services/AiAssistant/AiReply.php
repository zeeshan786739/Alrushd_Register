<?php

namespace App\Services\AiAssistant;

final class AiReply
{
    /**
     * @param  list<array{id: string, name: string, arguments: array<string, mixed>}>  $toolCalls
     * @param  array<string, mixed>  $message  the assistant message exactly as returned (sent back with tool results)
     */
    public function __construct(
        public readonly ?string $content,
        public readonly array $toolCalls,
        public readonly int $tokensIn,
        public readonly int $tokensOut,
        public readonly array $message,
    ) {}
}
