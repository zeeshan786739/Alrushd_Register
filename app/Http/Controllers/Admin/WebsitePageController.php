<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteCms;
use App\Models\WebsitePage;
use App\Services\HeadlessWebsiteService;
use App\Support\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pages of a connected (headless) website. Edits are drafts; the CMS "Publish"
 * button publishes site settings and every changed page together.
 */
class WebsitePageController extends Controller
{
    private const RESERVED_PREFIXES = ['/api', '/_next', '/admin', '/w/'];

    public function __construct(private HeadlessWebsiteService $headless)
    {
        $this->middleware('permission:view setting')->only(['index']);
        $this->middleware('permission:edit setting')->except(['index']);
    }

    public function index(): JsonResponse
    {
        return response()->json(['pages' => $this->list()]);
    }

    public function store(Request $request): JsonResponse
    {
        $orgId = OrganizationContext::idOrFail();
        $draft = WebsitePage::normaliseDraft((array) $request->input('draft', []), WebsitePage::KIND_CUSTOM);

        if ($error = $this->validateDraft($draft, $orgId)) {
            return response()->json(['message' => $error], 422);
        }

        $page = WebsitePage::create([
            'organization_id' => $orgId,
            'kind' => WebsitePage::KIND_CUSTOM,
            'draft' => $draft,
            'sort_order' => (int) WebsitePage::where('organization_id', $orgId)->max('sort_order') + 1,
        ]);

        return response()->json(['message' => 'Page created as a draft. Publish to make it live.', 'page' => $page->toAdminArray(), 'pages' => $this->list()]);
    }

    public function update(Request $request, WebsitePage $page): JsonResponse
    {
        $this->authorizePage($page);
        $draft = WebsitePage::normaliseDraft((array) $request->input('draft', []), $page->kind);

        // A system page's path is fixed by the website's code.
        if ($page->kind === WebsitePage::KIND_SYSTEM) {
            $draft['path'] = $page->draft['path'] ?? $draft['path'];
        }

        if ($error = $this->validateDraft($draft, $page->organization_id, $page->id)) {
            return response()->json(['message' => $error], 422);
        }

        $page->update(['draft' => $draft]);

        return response()->json(['message' => 'Page saved as a draft.', 'page' => $page->toAdminArray(), 'pages' => $this->list()]);
    }

    public function destroy(WebsitePage $page): JsonResponse
    {
        $this->authorizePage($page);

        if ($page->kind === WebsitePage::KIND_SYSTEM) {
            return response()->json(['message' => 'Built-in pages cannot be deleted. Hide them instead.'], 422);
        }

        $wasLive = $page->isPublished();
        $page->delete();

        if ($wasLive) {
            $this->headless->revalidate(WebsiteCms::instance());
        }

        return response()->json(['message' => 'Page deleted.', 'pages' => $this->list()]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $orgId = OrganizationContext::idOrFail();
        $ids = array_map('intval', (array) $request->input('ids', []));

        DB::transaction(function () use ($ids, $orgId) {
            foreach ($ids as $index => $id) {
                WebsitePage::where('organization_id', $orgId)->whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json(['message' => 'Order saved.', 'pages' => $this->list()]);
    }

    private function list(): array
    {
        return WebsitePage::query()
            ->where('organization_id', OrganizationContext::idOrFail())
            ->orderBy('sort_order')->orderBy('id')
            ->get()
            ->map->toAdminArray()
            ->all();
    }

    private function validateDraft(array $draft, int $orgId, ?int $ignoreId = null): ?string
    {
        if ($draft['title'] === '') {
            return 'Please give the page a title.';
        }

        $path = $draft['path'];
        if ($path === '' || $path === '/') {
            if ($ignoreId === null || ! WebsitePage::whereKey($ignoreId)->where('kind', WebsitePage::KIND_SYSTEM)->exists()) {
                return 'Please choose a page URL, for example /summer-school.';
            }
        }

        foreach (self::RESERVED_PREFIXES as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, rtrim($prefix, '/').'/')) {
                return "The URL {$path} is reserved by the website.";
            }
        }

        $taken = WebsitePage::query()
            ->where('organization_id', $orgId)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->get()
            ->contains(fn (WebsitePage $p) => ($p->draft['path'] ?? null) === $path || $p->path === $path);

        return $taken ? "Another page already uses the URL {$path}." : null;
    }

    private function authorizePage(WebsitePage $page): void
    {
        abort_unless($page->organization_id === OrganizationContext::idOrFail(), 404);
    }
}
