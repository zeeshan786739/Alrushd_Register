<?php

namespace App\Services\AiAssistant;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Chat Completions with tool calling over plain HTTP. Works with OpenAI and any service that
 * offers the same API at another base URL.
 */
class OpenAiCompatibleProvider implements AiProvider
{
    public const TEMPERATURE = 0.2;

    public const MAX_TOKENS = 400;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly int $timeout = 30,
    ) {}

    public static function fromConfig(AiAssistantConfig $config): self
    {
        return new self((string) $config->apiKey, $config->model, $config->baseUrl);
    }

    public function chat(array $messages, array $tools): AiReply
    {
        if ($this->apiKey === '') {
            throw new AiProviderException('No API key is set.');
        }

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => self::TEMPERATURE,
            'max_tokens' => self::MAX_TOKENS,
        ];
        if ($tools) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        try {
            $response = Http::timeout($this->timeout)->withToken($this->apiKey)->acceptJson()
                ->post(rtrim($this->baseUrl, '/').'/chat/completions', $payload);
        } catch (Throwable $e) {
            throw new AiProviderException('The AI service could not be reached.', 0, $e);
        }

        if (! $response->successful()) {
            $detail = (string) ($response->json('error.message') ?? '');
            throw new AiProviderException(match (true) {
                $response->status() === 401 => 'The AI service refused the API key.',
                $response->status() === 404 => 'The AI service doesn\'t know this model or address.',
                $response->status() === 429 => 'The AI service is busy or the account is out of credit.',
                default => 'The AI service answered with HTTP '.$response->status().'.',
            }.($detail !== '' ? ' ('.mb_substr($detail, 0, 160).')' : ''));
        }

        $message = (array) $response->json('choices.0.message', []);
        $calls = [];
        foreach ((array) ($message['tool_calls'] ?? []) as $call) {
            $args = json_decode((string) ($call['function']['arguments'] ?? '{}'), true);
            $calls[] = [
                'id' => (string) ($call['id'] ?? ''),
                'name' => (string) ($call['function']['name'] ?? ''),
                'arguments' => is_array($args) ? $args : [],
            ];
        }

        return new AiReply(
            isset($message['content']) ? (string) $message['content'] : null,
            $calls,
            (int) $response->json('usage.prompt_tokens', 0),
            (int) $response->json('usage.completion_tokens', 0),
            ['role' => 'assistant', 'content' => $message['content'] ?? null]
                + (empty($message['tool_calls']) ? [] : ['tool_calls' => $message['tool_calls']]),
        );
    }
}
