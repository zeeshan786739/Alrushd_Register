<?php

namespace App\Services\AiAssistant;

/** A chat model with tool calling. Throws AiProviderException when the service fails. */
interface AiProvider
{
    /**
     * @param  list<array<string, mixed>>  $messages  OpenAI-style chat messages
     * @param  list<array<string, mixed>>  $tools  OpenAI-style function tools
     */
    public function chat(array $messages, array $tools): AiReply;
}
