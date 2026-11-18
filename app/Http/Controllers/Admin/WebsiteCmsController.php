<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\WebsiteCms;
use App\Models\WebsitePage;
use App\Services\HeadlessWebsiteService;
use App\Services\WebsiteCmsService;
use App\Services\Platform\PlatformActivityLogger;
use App\Support\AnalyticsSettings;
use App\Support\BrandingImages;
use App\Support\ContactDetails;
use App\Support\SeoSettings;
use App\Support\SocialProfiles;
use App\Support\WebsiteCmsValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WebsiteCmsController extends Controller
{
    public function __construct(
        protected WebsiteCmsService $cms,
        protected HeadlessWebsiteService $headless,
    ) {
        $this->middleware('permission:view setting')->only(['index', 'previewFrame', 'previewUrl', 'websiteFormStatus']);
        $this->middleware('permission:edit setting')->only([
            'saveDraft', 'publish', 'discard', 'resetSection', 'preview', 'upload', 'uploadBrandingImage', 'restoreVersion',
            'saveConnection', 'rotateToken', 'testConnection', 'revalidate',
        ]);
    }

    public function index(): View
    {
        $cms = WebsiteCms::instance();
        $draft = $this->cms->getDraft();
        $published = $cms->published ? $this->cms->merge($cms->published) : null;
        $hasUnpublished = $published && json_encode($draft) !== json_encode($published);
        $organization = auth('admin')->user()?->organization;
        $publicWebsiteUrl = $organization?->publicWebsiteUrl();
        $previewUrl = $organization
            ? \App\Support\OrganizationUrls::publicPath($organization, '/')
            : url('/');
        $headless = $cms->isHeadless();
        if ($headless) {
            $publicWebsiteUrl = $cms->externalBase();
            $previewUrl = $this->headless->previewUrl($cms, '/') ?? $cms->externalBase();
        }
        $pagesChanged = WebsitePage::where('organization_id', $cms->organization_id)->get()
            ->contains(fn (WebsitePage $p) => $p->hasUnpublishedChanges());

        return view('admin.website-cms.index', [
            'cmsData' => $draft,
            'publishedAt' => $cms->published_at,
            'hasUnpublished' => $hasUnpublished || $pagesChanged,
            'siteHasUnpublished' => (bool) $hasUnpublished,
            // Lets the Navigation menu editor show "matches the live website" and discard only the menu.
            'publishedNavMenu' => $published['nav_menu'] ?? null,
            'publishedFooterMenu' => $published['footer_menu'] ?? null,
            'publishedWebsiteForms' => $published ? ($published['website']['forms'] ?? []) : null,
            'publishedWebsite' => $published['website'] ?? null,
            'publishedContact' => $published['contact'] ?? null,
            'publishedSocial' => $published['social'] ?? null,
            'publishedBranding' => $published['branding'] ?? null,
            'publishedSeo' => $published['seo'] ?? null,
            'publishedAnalytics' => $published['analytics'] ?? null,
            'publishedCustomCode' => $published['custom_code'] ?? null,
            // Live preview panel (website-cms-preview.js)
            'previewPanel' => $headless ? [
                'connected' => (bool) ($cms->externalBase() && $cms->api_token),
                'origin' => HeadlessWebsiteService::origin($cms->externalBase()),
                'base' => $cms->externalBase(),
                'published' => $published,
                'connectionModule' => 'connection',
            ] : null,
            'canEditCode' => (bool) auth('admin')->user()?->can('edit website code'),
            'assistant' => $headless ? $this->assistantPayload($cms) : null,
            'customCodeLastPublished' => $this->lastCustomCodePublish($cms),
            'seoSuggestedAddress' => $headless ? SeoSettings::suggestedAddress($cms, $organization) : null,
            'websiteFormStatus' => $headless
                ? $this->headless->websiteFormStatus($cms->organization_id, $draft['website']['forms'] ?? [])
                : [],
            'headless' => $headless,
            'connection' => $this->connectionPayload($cms),
            'pages' => $headless
                ? WebsitePage::where('organization_id', $cms->organization_id)->orderBy('sort_order')->orderBy('id')->get()->map->toAdminArray()->all()
                : [],
            'availableForms' => $this->headless->availableForms($cms->organization_id),
            'versionHistory' => $cms->version_history ?? [],
            'modules' => $this->modules(),
            'publicWebsiteUrl' => $publicWebsiteUrl,
            'previewUrl' => $previewUrl,
        ]);
    }

    public function saveDraft(Request $request): JsonResponse
    {
        $data = $request->input('data', []);
        $data = is_array($data) ? $data : [];
        if ($invalid = $this->invalid($data)) {
            return $invalid;
        }
        $this->cms->saveDraft($data);

        return response()->json([
            'success' => true,
            'message' => 'Draft saved successfully.',
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function publish(Request $request): JsonResponse
    {
        if ($request->has('data')) {
            $data = (array) $request->input('data', []);
            if ($invalid = $this->invalid($data)) {
                return $invalid;
            }
            $this->cms->saveDraft($data);
        }

        $codeBefore = $this->customCodeOf($this->cms->getPublished());
        $cms = $this->cms->publish(Auth::guard('admin')->id());
        $this->logCustomCodeChange($codeBefore, $this->customCodeOf($this->cms->getPublished()), $cms);

        return response()->json([
            'success' => true,
            'message' => 'Website published successfully.',
            'published_at' => $cms->published_at?->toIso8601String(),
        ]);
    }

    public function discard(): JsonResponse
    {
        $this->cms->discardDraft();

        return response()->json([
            'success' => true,
            'message' => 'Draft discarded. Restored to last published version.',
            'data' => $this->cms->getDraft(),
        ]);
    }

    /** The CMS's save rules (shared with the AI assistant): null, or the 422/403 answer to send. */
    private function invalid(array $data): ?JsonResponse
    {
        $problem = app(WebsiteCmsValidator::class)->problem($data);

        return $problem
            ? response()->json(array_filter(['message' => $problem['message'], 'errors' => $problem['errors'] ?: null]), $problem['status'])
            : null;
    }

    /** @return array<string, mixed> */
    private function customCodeOf(array $settings): array
    {
        $code = $settings['custom_code'] ?? [];
        $out = [];
        foreach (WebsiteCmsValidator::CUSTOM_CODE_BLOCKS as $block => $flag) {
            $out[$block] = (string) ($code[$block] ?? '');
            $out[$flag] = filter_var($code[$flag] ?? true, FILTER_VALIDATE_BOOLEAN);
        }

        return $out;
    }

    /** Record who put custom code on the live website, and what changed (before and after). */
    private function logCustomCodeChange(array $before, array $after, WebsiteCms $cms): void
    {
        if ($before === $after) {
            return;
        }
        $changes = [];
        foreach ($after as $key => $value) {
            if ($before[$key] !== $value) {
                $changes[$key] = ['before' => $before[$key], 'after' => $value];
            }
        }
        PlatformActivityLogger::log(
            'website.custom_code.published',
            'Published custom code changes: '.implode(', ', array_keys($changes)),
            $cms->organization,
            ['changes' => $changes],
        );
    }

    /** Website CMS → Branding: upload the connected website's logo or browser icon. */
    public function uploadBrandingImage(Request $request): JsonResponse
    {
        $kind = $request->input('kind');
        $request->validate([
            'kind' => 'required|in:logo,favicon',
            'file' => $kind === 'favicon'
                ? 'required|file|mimes:png,jpg,jpeg,webp|max:5120'
                : 'required|file|mimes:png,svg,webp|max:2048',
        ], [
            'file.mimes' => $kind === 'favicon' ? 'Please choose a PNG or JPG image.' : 'Please choose a PNG, SVG or WebP image.',
            'file.max' => $kind === 'favicon' ? 'That image is larger than 5 MB.' : 'That logo is larger than 2 MB.',
        ]);

        $organizationId = WebsiteCms::instance()->organization_id;
        try {
            $result = $kind === 'favicon'
                ? BrandingImages::storeFavicon($request->file('file'), $organizationId)
                : BrandingImages::storeLogo($request->file('file'), $organizationId);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => ['file' => [$e->getMessage()]]], 422);
        }

        return response()->json(['success' => true] + $result);
    }

    public function resetSection(Request $request): JsonResponse
    {
        $section = $request->input('section');
        $data = $this->cms->resetSection($section);

        return response()->json([
            'success' => true,
            'message' => 'Section reset to defaults.',
            'data' => $data,
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $data = $request->input('data', []);
        $adminId = Auth::guard('admin')->id();
        $this->cms->setLivePreview(is_array($data) ? $data : [], $adminId);

        return response()->json(['success' => true]);
    }

    public function upload(Request $request): JsonResponse
    {
        // A page's social sharing image is shown by WhatsApp/Facebook/LinkedIn, which
        // reliably support only JPG and PNG.
        $field = (string) $request->input('field');
        $isSharingImage = str_starts_with($field, 'page.seo.og_image') || $field === 'seo.og_image';

        $request->validate([
            'file' => $isSharingImage ? 'required|file|mimes:jpg,jpeg,png|max:5120' : 'required|image|max:5120',
            'field' => 'required|string|max:100',
        ], [
            'file.mimes' => 'Please choose a JPG or PNG image.',
            'file.image' => 'Please choose an image file.',
            'file.max' => 'That image is larger than 5 MB. Please choose a smaller one.',
        ]);

        $path = ImageHelper::uploadImage($request->file('file'));

        return response()->json([
            'success' => true,
            'url' => asset('storage/'.$path),
            'path' => $path,
            'field' => $request->input('field'),
        ]);
    }

    public function restoreVersion(Request $request): JsonResponse
    {
        $index = (int) $request->input('index', 0);
        $cms = $this->cms->restoreVersion($index);

        if (! $cms) {
            return response()->json(['success' => false, 'message' => 'Version not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Version restored to draft.',
            'data' => $this->cms->getDraft(),
        ]);
    }

    public function saveConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mode' => 'required|in:builtin,headless',
            'external_url' => 'nullable|url:https,http|max:255',
            'redirect_legacy' => 'boolean',
        ]);

        if ($validated['mode'] === WebsiteCms::MODE_HEADLESS && empty($validated['external_url'])) {
            return response()->json(['success' => false, 'message' => 'Enter the URL of the website to connect.'], 422);
        }

        $cms = WebsiteCms::instance();
        $cms->fill([
            'mode' => $validated['mode'],
            'external_url' => $validated['external_url'] ? rtrim($validated['external_url'], '/') : null,
            'redirect_legacy' => (bool) ($validated['redirect_legacy'] ?? true),
        ])->save();

        if ($cms->mode === WebsiteCms::MODE_HEADLESS && ! $cms->api_token) {
            $cms->rotateApiToken();
        }

        return response()->json([
            'success' => true,
            'message' => $cms->isHeadless() ? 'Website connected. Reloading the CMS…' : 'Switched back to the built-in website.',
            'connection' => $this->connectionPayload($cms->fresh()),
            'reload' => true,
        ]);
    }

    public function rotateToken(): JsonResponse
    {
        $cms = WebsiteCms::instance();
        $cms->rotateApiToken();

        return response()->json([
            'success' => true,
            'message' => 'New API token created. Update CRM_API_TOKEN on the website, then redeploy it.',
            'connection' => $this->connectionPayload($cms->fresh()),
        ]);
    }

    public function testConnection(Request $request): JsonResponse
    {
        $result = $this->headless->testConnection(
            WebsiteCms::instance(),
            HeadlessWebsiteService::origin($request->getSchemeAndHttpHost()),
            $request->getSchemeAndHttpHost().'/api/headless/v1',
        );

        return response()->json(['success' => $result['ok'], 'code' => $result['code'], 'message' => $result['message']]);
    }

    /** Signed draft-preview link for one page of the connected website. */
    /** Preview links for the CMS: ?mode=draft (default, unpublished changes) or ?mode=live (published site). */
    public function previewUrl(Request $request): JsonResponse
    {
        $path = WebsitePage::normalisePath((string) $request->query('path', '/'));
        $cms = WebsiteCms::instance();
        $url = $request->query('mode') === 'live'
            ? $this->headless->liveFrameUrl($cms, $path)
            : $this->headless->previewUrl($cms, $path);

        return response()->json(['url' => $url, 'origin' => HeadlessWebsiteService::origin($cms->externalBase())]);
    }

    /** Live check for Website CMS → Website forms: does each chosen Form Center form fit its website form? */
    public function websiteFormStatus(Request $request): JsonResponse
    {
        $assigned = $request->input('forms', []);

        return response()->json([
            'forms' => $this->headless->websiteFormStatus(
                WebsiteCms::instance()->organization_id,
                is_array($assigned) ? $assigned : [],
            ),
        ]);
    }

    public function revalidate(): JsonResponse
    {
        $cms = WebsiteCms::instance();
        $ok = $this->headless->revalidate($cms);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'The website refreshed its content.' : 'The website could not be refreshed ('.($cms->fresh()->last_revalidate_status ?? 'not connected').').',
        ]);
    }

    /** @return array{by: string, at: string}|null */
    private function lastCustomCodePublish(WebsiteCms $cms): ?array
    {
        $log = \App\Models\PlatformActivityLog::query()
            ->where('organization_id', $cms->organization_id)
            ->where('action', 'website.custom_code.published')
            ->latest('created_at')
            ->with('admin:id,name')
            ->first();

        return $log ? ['by' => $log->admin?->name ?? 'Someone', 'at' => $log->created_at?->toIso8601String()] : null;
    }

    protected function connectionPayload(WebsiteCms $cms): array
    {
        return [
            'mode' => $cms->mode ?? WebsiteCms::MODE_BUILTIN,
            'external_url' => $cms->external_url,
            'redirect_legacy' => (bool) ($cms->redirect_legacy ?? true),
            'api_token' => $cms->api_token,
            'api_base' => url('/api/headless/v1'),
            'crm_origin' => url('/'),
            'last_revalidated_at' => $cms->last_revalidated_at?->diffForHumans(),
            'last_revalidate_status' => $cms->last_revalidate_status,
        ];
    }

    protected function modules(): array
    {
        if (WebsiteCms::instance()->isHeadless()) {
            return $this->headlessModules();
        }

        return [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'solar:widget-5-linear', 'group' => 'Overview', 'description' => 'Quick links and publish overview'],
            ['id' => 'connection', 'label' => 'Website Connection', 'icon' => 'solar:link-round-linear', 'group' => 'Overview', 'description' => 'Use the built-in site or connect an external website'],
            ['id' => 'branding', 'label' => 'Branding', 'icon' => 'solar:palette-linear', 'group' => 'Appearance', 'description' => 'Logo and brand identity'],
            ['id' => 'theme', 'label' => 'Theme', 'icon' => 'solar:colour-tuneing-linear', 'group' => 'Appearance', 'description' => 'Colors and visual theme'],
            ['id' => 'typography', 'label' => 'Typography', 'icon' => 'solar:text-field-linear', 'group' => 'Appearance', 'description' => 'Fonts and text styles'],
            ['id' => 'buttons', 'label' => 'Buttons', 'icon' => 'solar:cursor-square-linear', 'group' => 'Appearance', 'description' => 'Button styles and labels'],
            ['id' => 'animations', 'label' => 'Animations', 'icon' => 'solar:magic-stick-3-linear', 'group' => 'Appearance', 'description' => 'Motion and transitions'],
            ['id' => 'navbar', 'label' => 'Header / Navbar', 'icon' => 'solar:hamburger-menu-linear', 'group' => 'Page', 'description' => 'Site header and navigation'],
            ['id' => 'hero', 'label' => 'Hero Section', 'icon' => 'solar:gallery-wide-linear', 'group' => 'Page', 'description' => 'Landing page hero content'],
            ['id' => 'homepage_sections', 'label' => 'Homepage Sections', 'icon' => 'solar:sort-vertical-linear', 'group' => 'Page', 'description' => 'Reorder and toggle sections'],
            ['id' => 'about', 'label' => 'About', 'icon' => 'solar:info-circle-linear', 'group' => 'Page', 'description' => 'About section content'],
            ['id' => 'features', 'label' => 'Why Choose Us', 'icon' => 'solar:star-linear', 'group' => 'Page', 'description' => 'Feature highlights'],
            ['id' => 'forms_section', 'label' => 'Forms', 'icon' => 'solar:document-text-linear', 'group' => 'Page', 'description' => 'Forms section on the landing page'],
            ['id' => 'statistics', 'label' => 'Statistics', 'icon' => 'solar:chart-2-linear', 'group' => 'Page', 'description' => 'Stats and counters'],
            ['id' => 'programs', 'label' => 'Programs', 'icon' => 'solar:book-2-linear', 'group' => 'Page', 'description' => 'Programs and courses'],
            ['id' => 'gallery', 'label' => 'Gallery', 'icon' => 'solar:gallery-linear', 'group' => 'Page', 'description' => 'Image gallery'],
            ['id' => 'how_it_works', 'label' => 'How It Works', 'icon' => 'solar:route-linear', 'group' => 'Page', 'description' => 'Step-by-step process'],
            ['id' => 'testimonials', 'label' => 'Testimonials', 'icon' => 'solar:chat-round-like-linear', 'group' => 'Page', 'description' => 'Reviews and quotes'],
            ['id' => 'faq', 'label' => 'FAQ', 'icon' => 'solar:question-circle-linear', 'group' => 'Page', 'description' => 'Frequently asked questions'],
            ['id' => 'contact', 'label' => 'Contact', 'icon' => 'solar:phone-linear', 'group' => 'Page', 'description' => 'Contact details and CTA'],
            ['id' => 'footer', 'label' => 'Footer', 'icon' => 'solar:layers-minimalistic-linear', 'group' => 'Page', 'description' => 'Footer links and copy'],
            ['id' => 'social', 'label' => 'Social Media', 'icon' => 'solar:share-linear', 'group' => 'Page', 'description' => 'Social profile links'],
            ['id' => 'seo', 'label' => 'SEO', 'icon' => 'solar:magnifer-linear', 'group' => 'Growth', 'description' => 'Meta tags and search settings'],
            ['id' => 'analytics', 'label' => 'Analytics', 'icon' => 'solar:graph-linear', 'group' => 'Growth', 'description' => 'Tracking and analytics codes'],
            ['id' => 'custom_code', 'label' => 'Custom Code', 'icon' => 'solar:code-linear', 'group' => 'Advanced', 'description' => 'Custom CSS and scripts'],
            ['id' => 'system', 'label' => 'System', 'icon' => 'solar:settings-linear', 'group' => 'Advanced', 'description' => 'CMS system options'],
        ];
    }

    /**
     * Modules that apply to a connected external website. Landing-page-only sections
     * (hero, programs, gallery…) belong to the built-in site and are hidden here.
     */
    /** The AI assistant drawer and settings screen (website-cms-assistant.js). The API key never leaves the server. */
    protected function assistantPayload(WebsiteCms $cms): ?array
    {
        // Until `php artisan migrate` has run, the CMS works as before, without the assistant.
        if (! \Illuminate\Support\Facades\Schema::hasTable('ai_assistant_settings')) {
            return null;
        }
        $admin = auth('admin')->user();
        $config = \App\Services\AiAssistant\AiAssistantConfig::forOrganization($cms->organization_id);
        $canManage = (bool) $admin?->can('manage ai assistant');

        return [
            'canUse' => (bool) $admin?->can('edit setting'),
            'blocked' => AiAssistantController::blockedReason($config),
            'canManage' => $canManage,
            'settings' => $canManage ? AiAssistantController::settingsPayload($config) : null,
            'routes' => [
                'chat' => route('admin.website-cms.ai-assistant.chat'),
                'undo' => route('admin.website-cms.ai-assistant.undo'),
                'reset' => route('admin.website-cms.ai-assistant.reset'),
                'settings' => route('admin.website-cms.ai-assistant.settings.save'),
                'test' => route('admin.website-cms.ai-assistant.settings.test'),
            ],
        ];
    }

    protected function headlessModules(): array
    {
        $modules = [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'solar:widget-5-linear', 'group' => 'Overview', 'description' => 'Publish overview for your connected website'],
            ['id' => 'connection', 'label' => 'Website Connection', 'icon' => 'solar:link-round-linear', 'group' => 'Overview', 'description' => 'Connected website, API token and cache refresh'],
            ['id' => 'pages', 'label' => 'Pages', 'icon' => 'solar:documents-linear', 'group' => 'Content', 'description' => 'Create pages, manage navigation, SEO and connected forms'],
            ['id' => 'nav_menu', 'label' => 'Navigation Menu', 'icon' => 'solar:hamburger-menu-linear', 'group' => 'Content', 'description' => 'Header links, dropdowns and the header button'],
            ['id' => 'footer_menu', 'label' => 'Footer', 'icon' => 'solar:layers-minimalistic-linear', 'group' => 'Content', 'description' => 'Footer heading, text and link columns'],
            ['id' => 'site_forms', 'label' => 'Website Forms', 'icon' => 'solar:document-text-linear', 'group' => 'Content', 'description' => 'Which Form Center form receives each website form'],
            ['id' => 'website', 'label' => 'Announcement & Portal', 'icon' => 'solar:bell-linear', 'group' => 'Content', 'description' => 'Announcement bar and portal login link'],
            ['id' => 'contact', 'label' => 'Contact Details', 'icon' => 'solar:phone-linear', 'group' => 'Content', 'description' => 'Phone, email, address and WhatsApp used across the site'],
            ['id' => 'social', 'label' => 'Social Media', 'icon' => 'solar:share-linear', 'group' => 'Content', 'description' => 'Social profile links'],
            ['id' => 'branding', 'label' => 'Branding', 'icon' => 'solar:palette-linear', 'group' => 'Appearance', 'description' => 'School name, tagline and copyright'],
            ['id' => 'theme', 'label' => 'Theme Colours', 'icon' => 'solar:colour-tuneing-linear', 'group' => 'Appearance', 'description' => 'Brand colours used by the website'],
            ['id' => 'seo', 'label' => 'SEO', 'icon' => 'solar:magnifer-linear', 'group' => 'Growth', 'description' => 'Default meta tags and search settings'],
            ['id' => 'analytics', 'label' => 'Analytics', 'icon' => 'solar:graph-linear', 'group' => 'Growth', 'description' => 'Tracking and analytics codes'],
            ['id' => 'custom_code', 'label' => 'Custom Code', 'icon' => 'solar:code-linear', 'group' => 'Advanced', 'description' => 'Custom CSS and scripts'],
        ];
        if (\Illuminate\Support\Facades\Schema::hasTable('ai_assistant_settings') && auth('admin')->user()?->can('manage ai assistant')) {
            $modules[] = ['id' => 'ai_assistant', 'label' => 'AI Assistant', 'icon' => 'solar:magic-stick-3-linear', 'group' => 'Advanced', 'description' => 'Switch the assistant on, its API key, model and monthly limit'];
        }

        return $modules;
    }
}
