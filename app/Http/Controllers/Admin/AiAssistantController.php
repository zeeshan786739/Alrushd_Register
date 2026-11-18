<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AiAssistantSetting;
use App\Models\WebsiteCms;
use App\Services\AiAssistant\AiAssistantConfig;
use App\Services\AiAssistant\AiProvider;
use App\Services\AiAssistant\AiProviderException;
use App\Services\AiAssistant\CmsAssistant;
use App\Services\AiAssistant\CmsAssistantTools;
use App\Services\AiAssistant\OpenAiCompatibleProvider;
use App\Services\HeadlessWebsiteService;
use App\Services\WebsiteCmsService;
use App\Support\WebsiteCmsValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Website CMS → AI assistant. Chat, Undo and history reset need "edit setting" (the same as
 * editing the CMS by hand); the settings screen needs "manage ai assistant". There is no publish
 * endpoint here: only the CMS's Publish button publishes.
 */
class AiAssistantController extends Controller
{
    public const MESSAGES_PER_HOUR = 30;

    public const UNAVAILABLE = 'The AI service isn’t responding — your site wasn’t changed. Try again.';

    public function __construct()
    {
        $this->middleware('permission:edit setting')->only(['chat', 'undo', 'reset']);
        $this->middleware('permission:manage ai assistant')->only(['settings', 'saveSettings', 'testSettings']);
    }

    private function admin(): Admin
    {
        return auth('admin')->user();
    }

    private function config(): AiAssistantConfig
    {
        return AiAssistantConfig::forOrganization(WebsiteCms::instance()->organization_id);
    }

    private function tools(): CmsAssistantTools
    {
        return new CmsAssistantTools(app(WebsiteCmsService::class), app(WebsiteCmsValidator::class), app(HeadlessWebsiteService::class), $this->admin());
    }

    /** Why the assistant can't be used right now, or null. */
    public static function blockedReason(AiAssistantConfig $config): ?string
    {
        return match (true) {
            ! WebsiteCms::instance()->isHeadless() => 'The AI assistant works with a connected website.',
            ! $config->enabled => 'The AI assistant is switched off. An administrator can switch it on in Website CMS → AI assistant.',
            ! $config->hasKey() => 'The AI assistant has no API key yet. An administrator can add one in Website CMS → AI assistant.',
            $config->limitReached() => 'This month’s AI assistant limit has been reached, so it’s paused until next month. An administrator can raise the limit.',
            default => null,
        };
    }

    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'section' => ['nullable', 'string', 'max:40'],
        ]);
        $config = $this->config();
        if ($reason = self::blockedReason($config)) {
            return response()->json(['message' => $reason, 'blocked' => true], 423);
        }
        $key = 'ai-assistant:'.$this->admin()->id;
        if (RateLimiter::tooManyAttempts($key, self::MESSAGES_PER_HOUR)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

            return response()->json(['message' => "You’ve sent a lot of messages. Try again in {$minutes} minute".($minutes === 1 ? '' : 's').'.'], 429);
        }
        RateLimiter::hit($key, 3600);

        $section = $data['section'] ?? null;
        $section = $section && (isset(CmsAssistantTools::SECTIONS[$section]) || $section === 'pages') ? $section : null;

        try {
            $result = (new CmsAssistant(app(AiProvider::class), $config, $this->tools(), $this->admin()))->handle($data['message'], $section);
        } catch (AiProviderException) {
            return response()->json(['message' => self::UNAVAILABLE], 502);
        }

        return response()->json($this->withData($result));
    }

    public function undo(Request $request): JsonResponse
    {
        $data = $request->validate(['change_set' => ['required', 'string', 'size:36']]);
        $tools = $this->tools();
        $result = $tools->undo($data['change_set']);
        if (! $result['restored'] && ! $result['errors']) {
            return response()->json(['message' => 'These changes were already undone.'], 422);
        }

        return response()->json($this->withData([
            'message' => $result['errors']
                ? 'Some changes couldn’t be undone: '.implode(' ', $result['errors'])
                : 'Undone. The previous values are back in your draft.',
            'errors' => $result['errors'],
            'data_keys' => $tools->touchedDataKeys(),
            'pages_changed' => $tools->touchedPages(),
        ]));
    }

    public function reset(): JsonResponse
    {
        CmsAssistant::forget(WebsiteCms::instance()->organization_id, $this->admin()->id);

        return response()->json(['ok' => true]);
    }

    /** Add the fresh draft of every changed section (and the page list) so the CMS screen can reload them. */
    private function withData(array $result): array
    {
        $draft = app(WebsiteCmsService::class)->getDraft();
        $result['data'] = array_intersect_key($draft, array_flip($result['data_keys'] ?? []));
        if (! empty($result['pages_changed'])) {
            $result['pages'] = \App\Models\WebsitePage::where('organization_id', WebsiteCms::instance()->organization_id)
                ->orderBy('sort_order')->orderBy('id')->get()->map->toAdminArray()->all();
        }
        unset($result['data_keys']);

        return $result;
    }

    /* ───────────────────────── settings (admins only) ───────────────────────── */

    public static function settingsPayload(AiAssistantConfig $config): array
    {
        $saved = AiAssistantSetting::forOrganization($config->organizationId);

        return [
            'enabled' => $config->enabled,
            'has_key' => $config->hasKey(),
            'key_last4' => $config->keyLast4(),
            'key_from_env' => $config->keyFromEnv,
            'model' => $config->model,
            'saved_model' => (string) $saved->model,
            'base_url' => $config->baseUrl,
            'limit_type' => $config->limitType,
            'monthly_limit' => $config->monthlyLimit,
            'used' => $config->usedThisMonth(),
            'limit_reached' => $config->limitReached(),
            'models' => AiAssistantConfig::MODELS,
        ];
    }

    public function settings(): JsonResponse
    {
        return response()->json(self::settingsPayload($this->config()));
    }

    private function settingsRules(): array
    {
        return [
            'api_key' => ['nullable', 'string', 'max:300'],
            'model' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9._:\/-]+$/'],
            'base_url' => ['nullable', 'string', 'max:255', 'url:https,http'],
        ];
    }

    public function saveSettings(Request $request): JsonResponse
    {
        $data = $request->validate($this->settingsRules() + [
            'enabled' => ['required', 'boolean'],
            'clear_key' => ['nullable', 'boolean'],
            'limit_type' => ['required', 'in:tokens,usd'],
            'monthly_limit' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
        ], [
            'model.regex' => 'Use the model name exactly as your provider writes it, like gpt-4o-mini.',
            'base_url.url' => 'Enter the full address, like https://api.openai.com/v1',
        ]);

        $setting = AiAssistantSetting::forOrganization(WebsiteCms::instance()->organization_id);
        $setting->fill([
            'enabled' => (bool) $data['enabled'],
            'model' => trim((string) ($data['model'] ?? '')) ?: null,
            'base_url' => rtrim(trim((string) ($data['base_url'] ?? '')), '/') ?: null,
            'limit_type' => $data['limit_type'],
            'monthly_limit' => isset($data['monthly_limit']) && $data['monthly_limit'] !== '' ? (float) $data['monthly_limit'] : null,
        ]);
        // An empty key field keeps the saved key; it is never sent back to the browser.
        if (! empty($data['clear_key'])) {
            $setting->api_key = null;
        } elseif (trim((string) ($data['api_key'] ?? '')) !== '') {
            $setting->api_key = trim($data['api_key']);
        }
        $setting->save();

        $config = $this->config();

        return response()->json(['message' => 'AI assistant settings saved.', 'settings' => self::settingsPayload($config), 'blocked' => self::blockedReason($config)]);
    }

    /** "Test connection": a tiny request with the values on screen (a typed key, or the saved one). */
    public function testSettings(Request $request): JsonResponse
    {
        $data = $request->validate($this->settingsRules());
        $config = $this->config();
        $provider = new OpenAiCompatibleProvider(
            trim((string) ($data['api_key'] ?? '')) ?: (string) $config->apiKey,
            trim((string) ($data['model'] ?? '')) ?: $config->model,
            rtrim(trim((string) ($data['base_url'] ?? '')) ?: $config->baseUrl, '/'),
            15,
        );

        try {
            $provider->chat([['role' => 'user', 'content' => 'Reply with the word OK.']], []);
        } catch (AiProviderException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Connected. The AI service answered.']);
    }
}
