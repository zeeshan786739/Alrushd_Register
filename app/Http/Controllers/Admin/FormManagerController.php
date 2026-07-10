<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormEntry;
use App\Services\FormBuilderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            'landing_forms' => $forms->where('show_on_landing', true)->count(),
            'total_submissions' => $forms->sum('entries_count'),
        ];

        return view('admin.form-manager.index', compact('forms', 'stats'));
    }

    public function create(): View
    {
        $form = new Form();

        return view('admin.form-manager.builder', [
            'form' => $form,
            'schema' => ['steps' => []],
            'fieldTypes' => config('form_options.field_types'),
            'optionSources' => $this->optionSources(),
        ]);
    }

    public function edit(Form $form): View
    {
        $form->load(['steps.fields']);

        return view('admin.form-manager.builder', [
            'form' => $form,
            'schema' => $this->builder->toSchema($form),
            'fieldTypes' => config('form_options.field_types'),
            'optionSources' => $this->optionSources(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validated($request);
        $payload['is_active'] = $request->boolean('is_active');
        $payload['show_on_landing'] = $request->boolean('show_on_landing');
        $form = $this->builder->syncForm($payload);

        return redirect()->route('admin.form-manager.edit', $form)->with('success', 'Form created successfully.');
    }

    public function update(Request $request, Form $form): RedirectResponse
    {
        $payload = $this->validated($request);
        $payload['is_active'] = $request->boolean('is_active');
        $payload['show_on_landing'] = $request->boolean('show_on_landing');
        $this->builder->syncForm($payload, $form);

        return redirect()->route('admin.form-manager.edit', $form)->with('success', 'Form updated successfully.');
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

    public function toggleActive(Form $form): RedirectResponse
    {
        $form->update(['is_active' => ! $form->is_active]);

        return back()->with('success', 'Form status updated.');
    }

    public function toggleLanding(Form $form): RedirectResponse
    {
        $form->update(['show_on_landing' => ! $form->show_on_landing]);

        return back()->with('success', 'Landing page visibility updated.');
    }

    private function validated(Request $request): array
    {
        if ($request->filled('schema') && ! $request->has('steps')) {
            $schema = json_decode($request->input('schema'), true);
            if (is_array($schema)) {
                $request->merge(['steps' => $schema['steps'] ?? []]);
            }
        }

        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'legacy_route' => 'nullable|string|max:255',
            'legacy_table' => 'nullable|string|max:255',
            'success_route' => 'nullable|string|max:255',
            'submit_method' => 'required|in:urlencoded,multipart',
            'is_active' => 'nullable|boolean',
            'show_on_landing' => 'nullable|boolean',
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
        ]);
    }

    private function optionSources(): array
    {
        return [
            '' => 'Static options only',
            'countries' => 'Countries',
            'debit_groups' => 'Debit groups',
            'genders' => 'Genders',
            'ethnicity' => 'Ethnicity',
            'job_marital' => 'Job marital status',
            'staff_marital' => 'Staff marital status',
            'job_departments' => 'Job departments',
            'employment_types' => 'Employment types',
            'working_hours' => 'Working hours',
            'hear_about_job' => 'How did you hear about job',
            'job_positions' => 'Job positions',
            'yes_no' => 'Yes / No',
        ];
    }
}
