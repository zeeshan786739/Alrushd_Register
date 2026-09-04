<?php

namespace App\Http\Controllers\Admin\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Models\EmailMarketing\Template;
use App\Services\EmailMarketing\HtmlSanitizer;
use App\Services\EmailMarketing\TemplateRenderer;
use App\Support\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __construct(
        private HtmlSanitizer $sanitizer,
        private TemplateRenderer $renderer,
    ) {
        $this->middleware('permission:view templates')->only(['index', 'preview']);
        $this->middleware('permission:create templates')->only(['create', 'store', 'duplicate']);
        $this->middleware('permission:update templates')->only(['edit', 'update']);
        $this->middleware('permission:delete templates')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $templates = Template::forCurrentOrganization()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.email-marketing.templates.index', compact('templates'));
    }

    public function create(): View
    {
        $templates = Template::forCurrentOrganization()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.email-marketing.templates.create', compact('templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Template::create([
            ...$validated,
            'organization_id' => OrganizationContext::idOrFail(),
            'body_html' => $this->sanitizer->sanitize($validated['body_html'] ?? ''),
            'body_text' => $validated['body_text'] ?? $this->sanitizer->toPlainText($validated['body_html'] ?? ''),
            'created_by' => auth('admin')->id(),
        ]);

        return redirect()->route('admin.email.templates.index')->with('success', 'Template created.');
    }

    public function edit(Template $emTemplate): View
    {
        $this->authorize('update', $emTemplate);

        return view('admin.email-marketing.templates.edit', ['template' => $emTemplate]);
    }

    public function update(Request $request, Template $emTemplate): RedirectResponse
    {
        $this->authorize('update', $emTemplate);
        $validated = $this->validated($request);

        $emTemplate->update([
            ...$validated,
            'body_html' => $this->sanitizer->sanitize($validated['body_html'] ?? ''),
            'body_text' => $validated['body_text'] ?? $this->sanitizer->toPlainText($validated['body_html'] ?? ''),
        ]);

        return redirect()->route('admin.email.templates.index')->with('success', 'Template updated.');
    }

    public function destroy(Template $emTemplate): RedirectResponse
    {
        $this->authorize('delete', $emTemplate);
        $emTemplate->delete();

        return redirect()->route('admin.email.templates.index')->with('success', 'Template deleted.');
    }

    public function preview(Template $emTemplate): View
    {
        $this->authorize('view', $emTemplate);
        $html = $this->renderer->render($emTemplate->body_html ?? '', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'company' => 'Example Co',
            'unsubscribe_url' => url('/unsubscribe-preview'),
        ]);

        return view('admin.email-marketing.templates.preview', [
            'template' => $emTemplate,
            'html' => $this->sanitizer->sanitize($html),
        ]);
    }

    public function body(Template $emTemplate): JsonResponse
    {
        $this->authorize('view', $emTemplate);

        return response()->json([
            'id' => $emTemplate->id,
            'name' => $emTemplate->name,
            'subject' => $emTemplate->subject,
            'category' => $emTemplate->category,
            'body_html' => $emTemplate->body_html,
        ]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        abort_unless(
            $request->user('admin')?->can('create templates') || $request->user('admin')?->can('update templates'),
            403
        );

        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $path = $validated['image']->store(
            'email-marketing/'.OrganizationContext::idOrFail(),
            'public'
        );

        return response()->json([
            'url' => url(Storage::disk('public')->url($path)),
        ]);
    }

    public function duplicate(Template $emTemplate): RedirectResponse
    {
        $this->authorize('create', Template::class);
        $copy = $emTemplate->replicate();
        $copy->name = $emTemplate->name.' (Copy)';
        $copy->created_by = auth('admin')->id();
        $copy->save();

        return redirect()->route('admin.email.templates.edit', $copy)->with('success', 'Template duplicated.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'nullable|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
