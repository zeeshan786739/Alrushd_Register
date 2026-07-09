<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\WebsiteCms;
use App\Services\WebsiteCmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WebsiteCmsController extends Controller
{
    public function __construct(
        protected WebsiteCmsService $cms
    ) {
        $this->middleware('permission:view setting')->only(['index', 'previewFrame']);
        $this->middleware('permission:edit setting')->only([
            'saveDraft', 'publish', 'discard', 'resetSection', 'preview', 'upload', 'restoreVersion',
        ]);
    }

    public function index(): View
    {
        $cms = WebsiteCms::instance();
        $draft = $this->cms->getDraft();
        $published = $cms->published ? $this->cms->merge($cms->published) : null;
        $hasUnpublished = $published && json_encode($draft) !== json_encode($published);

        return view('admin.website-cms.index', [
            'cmsData' => $draft,
            'publishedAt' => $cms->published_at,
            'hasUnpublished' => $hasUnpublished,
            'versionHistory' => $cms->version_history ?? [],
            'modules' => $this->modules(),
        ]);
    }

    public function saveDraft(Request $request): JsonResponse
    {
        $data = $request->input('data', []);
        $this->cms->saveDraft(is_array($data) ? $data : []);

        return response()->json([
            'success' => true,
            'message' => 'Draft saved successfully.',
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function publish(Request $request): JsonResponse
    {
        if ($request->has('data')) {
            $this->cms->saveDraft($request->input('data', []));
        }

        $cms = $this->cms->publish(Auth::guard('admin')->id());

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
        $request->validate([
            'file' => 'required|image|max:5120',
            'field' => 'required|string|max:100',
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

    protected function modules(): array
    {
        return [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'solar:widget-5-linear'],
            ['id' => 'branding', 'label' => 'Branding', 'icon' => 'solar:palette-linear'],
            ['id' => 'theme', 'label' => 'Theme', 'icon' => 'solar:colour-tuneing-linear'],
            ['id' => 'typography', 'label' => 'Typography', 'icon' => 'solar:text-field-linear'],
            ['id' => 'navbar', 'label' => 'Header / Navbar', 'icon' => 'solar:hamburger-menu-linear'],
            ['id' => 'hero', 'label' => 'Hero Section', 'icon' => 'solar:gallery-wide-linear'],
            ['id' => 'homepage_sections', 'label' => 'Homepage Sections', 'icon' => 'solar:sort-vertical-linear'],
            ['id' => 'about', 'label' => 'About', 'icon' => 'solar:info-circle-linear'],
            ['id' => 'features', 'label' => 'Why Choose Us', 'icon' => 'solar:star-linear'],
            ['id' => 'forms_section', 'label' => 'Forms', 'icon' => 'solar:document-text-linear'],
            ['id' => 'statistics', 'label' => 'Statistics', 'icon' => 'solar:chart-2-linear'],
            ['id' => 'programs', 'label' => 'Programs', 'icon' => 'solar:book-2-linear'],
            ['id' => 'gallery', 'label' => 'Gallery', 'icon' => 'solar:gallery-linear'],
            ['id' => 'how_it_works', 'label' => 'How It Works', 'icon' => 'solar:route-linear'],
            ['id' => 'testimonials', 'label' => 'Testimonials', 'icon' => 'solar:chat-round-like-linear'],
            ['id' => 'faq', 'label' => 'FAQ', 'icon' => 'solar:question-circle-linear'],
            ['id' => 'contact', 'label' => 'Contact', 'icon' => 'solar:phone-linear'],
            ['id' => 'footer', 'label' => 'Footer', 'icon' => 'solar:layers-minimalistic-linear'],
            ['id' => 'social', 'label' => 'Social Media', 'icon' => 'solar:share-linear'],
            ['id' => 'buttons', 'label' => 'Buttons', 'icon' => 'solar:cursor-square-linear'],
            ['id' => 'animations', 'label' => 'Animations', 'icon' => 'solar:magic-stick-3-linear'],
            ['id' => 'seo', 'label' => 'SEO', 'icon' => 'solar:magnifer-linear'],
            ['id' => 'analytics', 'label' => 'Analytics', 'icon' => 'solar:graph-linear'],
            ['id' => 'custom_code', 'label' => 'Custom Code', 'icon' => 'solar:code-linear'],
            ['id' => 'system', 'label' => 'System', 'icon' => 'solar:settings-linear'],
        ];
    }
}
