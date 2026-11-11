<?php

namespace App\Http\Controllers\Api\Headless;

use App\Http\Controllers\Api\DynamicFormController;
use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\WebsiteCms;
use App\Services\HeadlessWebsiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read API + form submission endpoint for an external (headless) website.
 * Every route runs behind AuthenticateHeadlessSite, so the organization is known.
 */
class HeadlessSiteController extends Controller
{
    public function __construct(private HeadlessWebsiteService $headless) {}

    public function ping(Request $request): JsonResponse
    {
        $cms = $this->cms($request);

        return response()->json([
            'ok' => true,
            'organization' => $cms->organization?->slug,
            'mode' => $cms->mode,
        ]);
    }

    public function site(Request $request): JsonResponse
    {
        return response()->json($this->headless->site($this->cms($request), $this->wantsDraft($request)));
    }

    public function pages(Request $request): JsonResponse
    {
        return response()->json(['pages' => $this->headless->pages($this->cms($request), $this->wantsDraft($request))]);
    }

    public function page(Request $request): JsonResponse
    {
        $page = $this->headless->page($this->cms($request), (string) $request->query('path', '/'), $this->wantsDraft($request));

        if (! $page) {
            return response()->json(['message' => 'Page not found.'], 404);
        }

        return response()->json(['page' => $page]);
    }

    /** Form schema, reusing the public dynamic-form endpoint (already tenant-scoped). */
    public function form(Request $request, string $slug): JsonResponse
    {
        $form = Form::scopedPublicQuery()->where('slug', $slug)->where('is_active', true)->first();
        if (! $form) {
            return response()->json(['message' => 'Form not found.'], 404);
        }

        $request->route()->setParameter('slug', $slug);

        return app(DynamicFormController::class)->show($request);
    }

    /**
     * Submissions are proxied by the website's server, so the visitor's IP arrives
     * in X-Visitor-IP. The caller is authenticated by token, so the header is trusted.
     */
    public function submit(Request $request, string $slug): JsonResponse
    {
        $visitorIp = (string) $request->header('X-Visitor-IP', '');
        if (filter_var($visitorIp, FILTER_VALIDATE_IP)) {
            $request->server->set('REMOTE_ADDR', $visitorIp);
        }

        $request->route()->setParameter('slug', $slug);

        return app(DynamicFormController::class)->submit($request);
    }

    private function cms(Request $request): WebsiteCms
    {
        return $request->attributes->get('headless_cms');
    }

    private function wantsDraft(Request $request): bool
    {
        return $request->boolean('draft');
    }
}
