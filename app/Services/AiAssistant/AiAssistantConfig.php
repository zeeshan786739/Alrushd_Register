<?php

namespace App\Services\AiAssistant;

use App\Models\AiAssistantRequest;
use App\Models\AiAssistantSetting;

/**
 * The assistant's settings for one school: the saved settings record, falling back to .env
 * (OPENAI_API_KEY, OPENAI_MODEL, OPENAI_BASE_URL) for anything left empty.
 */
final class AiAssistantConfig
{
    public const MODELS = [
        'gpt-4o-mini' => 'GPT-4o mini (default)',
        'gpt-4.1-nano' => 'GPT-4.1 nano (cheaper)',
        'gpt-4.1-mini' => 'GPT-4.1 mini (better at using tools)',
    ];

    /** USD per million tokens [in, out]. Unknown (custom) models are estimated at the dearest listed price. */
    public const PRICES = [
        'gpt-4o-mini' => [0.15, 0.60],
        'gpt-4.1-nano' => [0.10, 0.40],
        'gpt-4.1-mini' => [0.40, 1.60],
    ];

    public const DEFAULT_BASE_URL = 'https://api.openai.com/v1';

    public function __construct(
        public readonly int $organizationId,
        public readonly bool $enabled,
        public readonly ?string $apiKey,
        public readonly string $model,
        public readonly string $baseUrl,
        public readonly string $limitType,
        public readonly ?float $monthlyLimit,
        public readonly bool $keyFromEnv,
    ) {}

    public static function forOrganization(int $organizationId): self
    {
        $s = AiAssistantSetting::forOrganization($organizationId);
        $savedKey = trim((string) $s->api_key);

        return new self(
            $organizationId,
            (bool) $s->enabled,
            $savedKey !== '' ? $savedKey : (config('services.openai.key') ?: null),
            trim((string) $s->model) ?: (string) (config('services.openai.model') ?: 'gpt-4o-mini'),
            rtrim(trim((string) $s->base_url) ?: (string) (config('services.openai.base_url') ?: self::DEFAULT_BASE_URL), '/'),
            $s->limit_type === 'usd' ? 'usd' : 'tokens',
            $s->monthly_limit !== null && $s->monthly_limit > 0 ? (float) $s->monthly_limit : null,
            $savedKey === '' && (bool) config('services.openai.key'),
        );
    }

    public function hasKey(): bool
    {
        return (string) $this->apiKey !== '';
    }

    public function keyLast4(): ?string
    {
        return $this->hasKey() ? mb_substr((string) $this->apiKey, -4) : null;
    }

    public static function cost(string $model, int $in, int $out): float
    {
        [$pin, $pout] = self::PRICES[$model] ?? self::PRICES['gpt-4.1-mini'];

        return round(($in * $pin + $out * $pout) / 1_000_000, 6);
    }

    /** @return array{tokens: int, usd: float} */
    public function usedThisMonth(): array
    {
        $row = AiAssistantRequest::query()
            ->where('organization_id', $this->organizationId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('COALESCE(SUM(tokens_in + tokens_out), 0) as tokens, COALESCE(SUM(cost_usd), 0) as usd')
            ->first();

        return ['tokens' => (int) ($row->tokens ?? 0), 'usd' => round((float) ($row->usd ?? 0), 4)];
    }

    public function limitReached(): bool
    {
        if ($this->monthlyLimit === null) {
            return false;
        }
        $used = $this->usedThisMonth();

        return ($this->limitType === 'usd' ? $used['usd'] : $used['tokens']) >= $this->monthlyLimit;
    }
}
