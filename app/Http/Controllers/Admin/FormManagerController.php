<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormEntry;
use App\Services\FormBuilderService;
use App\Services\FormOptionsResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FormManagerController extends Controller
{
    public function __construct(private FormBuilderService $builder) {}

    public function index(): View
    {
        $forms = Form::query()
            ->withCount(['entries', 'steps', 'fields'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $stats = [
            'total_forms' => $forms->count(),
            'active_forms' => $forms->where('is_active', true)->count(),
            'landing_forms' => $forms->filter(fn (Form $form) => $form->hasPlacement('landing'))->count(),
            'total_submissions' => $forms->sum('entries_count'),
        ];

        return view('admin.form-manager.index', [
            'forms' => $forms,
            'stats' => $stats,
            'placementOptions' => config('form_options.placements', []),
        ]);
    }

    public function create(Request $request): View
    {
        $form = new Form();

        return view('admin.form-manager.builder', [
            'form' => $form,
            'schema' => [
                'steps' => [
                    [
                        'key' => 'step_1',
                        'title' => 'Step 1',
                        'description' => '',
                        'fields' => [],
                    ],
                ],
            ],
            'fieldTypes' => config('form_options.field_types'),
            'optionSources' => $this->optionSources(),
            'optionSourceGroups' => $this->optionSourceGroups(),
            'placementOptions' => config('form_options.placements', []),
            'wizardStep' => $this->resolveWizardStep($request),
        ]);
    }

    public function edit(Form $form, Request $request): View
    {
        $form->load(['steps.fields']);

        return view('admin.form-manager.builder', [
            'form' => $form,
            'schema' => $this->builder->toSchema($form),
            'fieldTypes' => config('form_options.field_types'),
            'optionSources' => $this->optionSources(),
            'optionSourceGroups' => $this->optionSourceGroups(),
            'placementOptions' => config('form_options.placements', []),
            'wizardStep' => $this->resolveWizardStep($request),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $payload = $this->validated($request, null);
        $payload['is_active'] = $request->boolean('is_active');
        $payload['placements'] = $request->input('placements', []);
        $payload['show_on_landing'] = in_array('landing', $payload['placements'], true);
        $form = $this->builder->syncForm($payload);

        if ($request->expectsJson()) {
            return $this->jsonAfterSave($form, $request, 'Form created successfully.');
        }

        return $this->redirectAfterSave($form, $request, 'Form created successfully.');
    }

    public function update(Request $request, Form $form): RedirectResponse|JsonResponse
    {
        $payload = $this->validated($request, $form);
        $payload['is_active'] = $request->boolean('is_active');
        $payload['placements'] = $request->input('placements', []);
        $payload['show_on_landing'] = in_array('landing', $payload['placements'], true);
        $this->builder->syncForm($payload, $form);

        if ($request->expectsJson()) {
            return $this->jsonAfterSave($form, $request, 'Form updated successfully.');
        }

        return $this->redirectAfterSave($form, $request, 'Form updated successfully.');
    }

    public function destroy(Form $form, Request $request): RedirectResponse|JsonResponse
    {
        $name = $form->name;
        $form->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Form "'.$name.'" deleted successfully.',
            ]);
        }

        return redirect()->route('admin.form-manager.index')->with('success', 'Form deleted.');
    }

    public function duplicate(Form $form): RedirectResponse
    {
        $form->load(['steps.fields']);
        $schema = $this->builder->toSchema($form);

        $schema['name'] = $form->name.' Copy';
        $schema['slug'] = $form->slug.'-copy-'.Str::lower(Str::random(4));
        $schema['legacy_route'] = null;
        $schema['legacy_table'] = null;
        $schema['show_on_landing'] = false;

        unset($schema['id']);
        foreach ($schema['steps'] as &$step) {
            unset($step['id']);
            foreach ($step['fields'] as &$field) {
                unset($field['id']);
            }
        }

        $copy = $this->builder->syncForm($schema);

        return redirect()->route('admin.form-manager.edit', $copy)->with('success', 'Form duplicated. Customize and publish when ready.');
    }

    public function entries(Form $form, Request $request): View
    {
        $query = $form->entries()->orderByDesc('submitted_at')->orderByDesc('id');

        if ($search = $request->get('search')) {
            $query->where('data', 'like', '%'.$search.'%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $dateFrom = $request->get('date_from') ?: $request->get('start_date');
        $dateTo = $request->get('date_to') ?: $request->get('end_date');
        if ($dateFrom) {
            $query->whereDate('submitted_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('submitted_at', '<=', $dateTo);
        }

        $entries = $query->paginate(15)->withQueryString();
        $form->load(['fields']);

        $entryStats = [
            'total' => $form->entries()->count(),
            'pending' => $form->entries()->where('status', 'pending')->count(),
            'approved' => $form->entries()->where('status', 'approved')->count(),
            'rejected' => $form->entries()->where('status', 'rejected')->count(),
        ];

        return view('admin.form-manager.entries.index', compact('form', 'entries', 'entryStats'));
    }

    public function entryShow(Form $form, FormEntry $entry): View
    {
        abort_unless($entry->form_id === $form->id, 404);
        $form->load(['fields']);

        return view('admin.form-manager.entries.show', compact('form', 'entry'));
    }

    public function entryUpdateStatus(Request $request, Form $form, FormEntry $entry): RedirectResponse
    {
        abort_unless($entry->form_id === $form->id, 404);

        $request->validate(['status' => 'required|in:pending,approved,rejected']);
        $entry->update(['status' => $request->status]);

        return back()->with('success', 'Submission status updated.');
    }

    public function entryDestroy(Form $form, FormEntry $entry): RedirectResponse
    {
        abort_unless($entry->form_id === $form->id, 404);
        $entry->delete();

        return redirect()->route('admin.form-manager.entries', $form)->with('success', 'Submission deleted.');
    }

    public function toggleActive(Request $request, Form $form): RedirectResponse|JsonResponse
    {
        $form->update(['is_active' => ! $form->is_active]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Form status updated.',
                'is_active' => $form->is_active,
            ]);
        }

        return back()->with('success', 'Form status updated.');
    }

    public function togglePlacement(Request $request, Form $form): RedirectResponse|JsonResponse
    {
        $allowed = Form::placementKeys();

        $validated = $request->validate([
            'placement' => 'required|string|in:'.implode(',', $allowed),
        ]);

        $placement = $validated['placement'];
        $placements = $form->placements();

        if (in_array($placement, $placements, true)) {
            $placements = array_values(array_filter($placements, fn ($p) => $p !== $placement));
        } else {
            $placements[] = $placement;
        }

        $form->setPlacements($placements);
        $form->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Display settings updated.',
                'placements' => $form->placements(),
            ]);
        }

        return back()->with('success', 'Display settings updated.');
    }

    public function toggleLanding(Form $form): RedirectResponse
    {
        $placements = $form->placements();
        if ($form->hasPlacement('landing')) {
            $placements = array_values(array_filter($placements, fn ($p) => $p !== 'landing'));
        } else {
            $placements[] = 'landing';
        }
        $form->setPlacements($placements);
        $form->save();

        return back()->with('success', 'Landing page visibility updated.');
    }

    public function updateSettings(Request $request, Form $form): RedirectResponse|JsonResponse
    {
        $allowed = Form::placementKeys();

        $validated = $request->validate([
            'placements' => 'nullable|array',
            'placements.*' => 'string|in:'.implode(',', $allowed),
        ]);

        $form->setPlacements($validated['placements'] ?? []);
        $form->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Display settings saved for '.$form->name.'.',
                'placements' => $form->placements(),
            ]);
        }

        return back()->with('success', 'Display settings saved for '.$form->name.'.');
    }

    private function validated(Request $request, ?Form $form = null): array
    {
        $steps = $this->resolveStepsFromRequest($request);

        if ($steps === []) {
            $steps = [
                [
                    'key' => 'step_1',
                    'title' => 'Step 1',
                    'description' => '',
                    'fields' => [],
                ],
            ];
        }

        $request->merge(['steps' => $steps]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('forms', 'slug')->ignore($form?->id),
            ],
            'description' => 'nullable|string',
            'legacy_route' => 'nullable|string|max:255',
            'legacy_table' => 'nullable|string|max:255',
            'success_route' => 'nullable|string|max:255',
            'submit_method' => 'required|in:urlencoded,multipart',
            'is_active' => 'nullable|boolean',
            'show_on_landing' => 'nullable|boolean',
            'placements' => 'nullable|array',
            'placements.*' => 'string|in:'.implode(',', Form::placementKeys()),
            'hero_label' => 'nullable|string|max:255',
            'hero_variant' => 'nullable|string|max:50',
            'handler' => 'nullable|in:dynamic,custom',
            'sort_order' => 'nullable|integer',
            'settings' => 'nullable|array',
            'steps' => 'required|array|min:1',
            'steps.*.title' => 'required|string|max:255',
            'steps.*.fields' => 'nullable|array',
            'steps.*.fields.*.key' => 'required|string|max:255',
            'steps.*.fields.*.label' => 'required|string|max:255',
            'steps.*.fields.*.type' => 'required|string|max:50',
            'steps.*.fields.*.placeholder' => 'nullable|string|max:255',
            'steps.*.fields.*.required' => 'nullable|boolean',
            'steps.*.fields.*.options' => 'nullable|array',
            'steps.*.fields.*.options.*' => 'nullable|string|max:255',
            'steps.*.fields.*.options_source' => 'nullable|string|max:255',
            'steps.*.fields.*.validation' => 'nullable',
            'steps.*.fields.*.col_span' => 'nullable|integer|min:1|max:2',
            'steps.*.fields.*.settings' => 'nullable|array',
        ]);

        if ($data['steps'] === []) {
            $data['steps'] = [
                [
                    'key' => 'step_1',
                    'title' => 'Step 1',
                    'description' => '',
                    'fields' => [],
                ],
            ];
        }

        return $data;
    }

    private function resolveStepsFromRequest(Request $request): array
    {
        if ($request->has('steps') && is_array($request->input('steps'))) {
            return $request->input('steps');
        }

        if ($request->filled('schema')) {
            $schema = json_decode($request->input('schema'), true);

            if (is_array($schema['steps'] ?? null)) {
                return $schema['steps'];
            }
        }

        return [];
    }

    private function redirectAfterSave(Form $form, Request $request, string $message): RedirectResponse
    {
        $currentStep = max(0, min(3, (int) $request->input('wizard_step', 0)));
        $nextStep = min($currentStep + 1, 3);

        return redirect()
            ->route('admin.form-manager.edit', $form)
            ->with('success', $message)
            ->with('wizard_step', $nextStep);
    }

    private function jsonAfterSave(Form $form, Request $request, string $message): JsonResponse
    {
        $currentStep = max(0, min(3, (int) $request->input('wizard_step', 0)));
        $nextStep = min($currentStep + 1, 3);

        return response()->json([
            'success' => true,
            'message' => $message,
            'wizard_step' => $nextStep,
            'update_url' => route('admin.form-manager.update', $form),
            'edit_url' => route('admin.form-manager.edit', $form),
        ]);
    }

    private function resolveWizardStep(Request $request): int
    {
        if ($request->old('wizard_step') !== null) {
            return max(0, min(3, (int) $request->old('wizard_step')));
        }

        if ($request->session()->has('wizard_step')) {
            return max(0, min(3, (int) $request->session()->get('wizard_step')));
        }

        return max(0, min(3, (int) $request->query('step', 0)));
    }

    private function optionSources(): array
    {
        return FormOptionsResolver::sourceLabels();
    }

    private function optionSourceGroups(): array
    {
        return FormOptionsResolver::sourceGroups();
    }

    public function optionSourceValues(string $source): JsonResponse
    {
        $labels = FormOptionsResolver::sourceLabels();
        abort_unless(array_key_exists($source, $labels) && $source !== '', 404);

        $options = app(FormOptionsResolver::class)->resolve($source);

        return response()->json([
            'source' => $source,
            'label' => $labels[$source],
            'options' => $options,
            'count' => count($options),
        ]);
    }
}
