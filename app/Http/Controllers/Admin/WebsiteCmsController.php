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
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'solar:widget-5-linear', 'group' => 'Overview', 'description' => 'Quick links and publish overview'],
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
}
