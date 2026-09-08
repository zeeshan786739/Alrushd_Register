<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\LeadImportRowStatus;
use App\Enums\LeadImportStatus;
use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Exports\Crm\LeadImportFailedRowsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\AssignLeadImportCategoryRequest;
use App\Http\Requests\Crm\ConfirmLeadImportRequest;
use App\Http\Requests\Crm\MapLeadImportRequest;
use App\Http\Requests\Crm\StoreLeadCategoryRequest;
use App\Http\Requests\Crm\UploadLeadImportRequest;
use App\Models\Admin;
use App\Models\Crm\LeadCategory;
use App\Models\Crm\LeadImport;
use App\Services\Crm\LeadImport\LeadImportService;
use App\Services\Crm\LeadImport\LeadImportUndoService;
use App\Support\LeadCategorySchema;
use App\Support\AdministrationSheetImportProfile;
use App\Support\LeadImportFields;
use App\Support\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeadImportController extends Controller
{
    public function __construct(
        private LeadImportService $imports,
        private LeadImportUndoService $undoService,
    )
    {
        $this->middleware('permission:import leads');
    }

    public function index(Request $request): View
    {
        $query = LeadImport::forCurrentOrganization()
            ->with(array_filter([
                'uploader',
                LeadCategorySchema::ready() ? 'category' : null,
            ]))
            ->withCount('leads')
            ->latest();

        if (LeadCategorySchema::ready() && $request->filled('lead_category_id')) {
            if ($request->lead_category_id === 'uncategorized') {
                $query->whereNull('lead_category_id');
            } else {
                $query->where('lead_category_id', (int) $request->lead_category_id);
            }
        }

        $imports = $query->paginate(20)->withQueryString();
        $categories = LeadCategorySchema::ready()
            ? LeadCategory::forCurrentOrganization()->orderBy('sort_order')->orderBy('name')->get()
            : collect();
        $importRemoval = $this->undoService->removableSummary();

        return view('admin.crm.leads.import.index', compact('imports', 'categories', 'importRemoval'));
    }

    public function create(): View
    {
        $categories = LeadCategorySchema::ready()
            ? LeadCategory::forCurrentOrganization()
                ->active()
                ->withCount('leads')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
            : collect();
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();

        return view('admin.crm.leads.import.create', compact('categories', 'admins'));
    }

    public function store(UploadLeadImportRequest $request): RedirectResponse
    {
        try {
            $import = $this->imports->createFromUpload($request->file('file'), $request->user('admin'));
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['file' => $e->getMessage()]);
        }

        $options = $import->import_options ?? [];
        if ($request->filled('default_assigned_to')) {
            $options['default_assigned_to'] = (int) $request->validated('default_assigned_to');
        }

        if (LeadCategorySchema::ready()) {
            $category = LeadCategory::forCurrentOrganization()
                ->active()
                ->whereKey((int) $request->validated('lead_category_id'))
                ->firstOrFail();

            $import->update([
                'lead_category_id' => $category->id,
                'import_options' => $options,
            ]);
        } elseif ($options !== ($import->import_options ?? [])) {
            $import->update(['import_options' => $options]);
        }

        $import = $import->fresh();
        $isAdministrationSheet = app(AdministrationSheetImportProfile::class)
            ->matches($import->detected_headers ?? []);

        if ($isAdministrationSheet) {
            $this->imports->saveMapping($import, [
                'selected_sheet' => $import->selected_sheet,
                'header_row' => $import->header_row,
                'mapping' => $import->mapping ?? [],
                'options' => $import->import_options ?? [],
            ]);

            $message = LeadCategorySchema::ready()
                ? 'File uploaded into “'.$category->name.'”. Review the preview and import.'
                : 'Administration sheet recognized. Review the preview and import.';

            return redirect()->route('admin.crm.leads.import.preview', $import->fresh())
                ->with('success', $message);
        }

        return redirect()->route('admin.crm.leads.import.map', $import)
            ->with('success', 'File uploaded. Review column mapping before importing.');
    }

    public function category(LeadImport $leadImport): View|RedirectResponse
    {
        if (! LeadCategorySchema::ready()) {
            return redirect()->route('admin.crm.leads.import.map', $leadImport);
        }

        $categories = LeadCategory::forCurrentOrganization()
            ->active()
            ->withCount('leads')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.crm.leads.import.category', [
            'import' => $leadImport,
            'categories' => $categories,
            'isAdministrationSheet' => app(AdministrationSheetImportProfile::class)
                ->matches($leadImport->detected_headers ?? []),
        ]);
    }

    public function saveCategory(AssignLeadImportCategoryRequest $request, LeadImport $leadImport): RedirectResponse
    {
        if (! LeadCategorySchema::ready()) {
            return redirect()->route('admin.crm.leads.import.map', $leadImport);
        }

        $category = LeadCategory::forCurrentOrganization()
            ->active()
            ->whereKey((int) $request->validated('lead_category_id'))
            ->firstOrFail();

        $leadImport->update(['lead_category_id' => $category->id]);

        return redirect()->route('admin.crm.leads.import.map', $leadImport)
            ->with('success', 'Category selected. Review column mapping before importing.');
    }

    public function storeCategory(StoreLeadCategoryRequest $request, ?LeadImport $leadImport = null): RedirectResponse|JsonResponse
    {
        if (! LeadCategorySchema::ready()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Lead categories are not available yet.'], 422);
            }

            return back()->withErrors(['name' => 'Lead categories are not available yet.']);
        }

        $category = LeadCategory::create([
            'organization_id' => OrganizationContext::idOrFail(),
            'name' => $request->validated('name'),
            'description' => $request->validated('description') ?? null,
            'icon' => $request->validated('icon') ?: \App\Support\LeadCategoryUi::DEFAULT_ICON,
            'tone' => $request->validated('tone') ?: \App\Support\LeadCategoryUi::DEFAULT_TONE,
            'is_active' => true,
        ]);

        if ($leadImport) {
            $leadImport->update(['lead_category_id' => $category->id]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Category “'.$category->name.'” created.',
                'category' => $this->categoryPayload($category),
            ]);
        }

        if ($leadImport) {
            return redirect()->route('admin.crm.leads.import.map', $leadImport)
                ->with('success', 'Category created and selected. Review column mapping before importing.');
        }

        return redirect()
            ->route('admin.crm.leads.import.create', ['category' => $category->id])
            ->with('success', 'Category “'.$category->name.'” created. Now upload your spreadsheet.');
    }

    public function destroyCategory(Request $request, LeadCategory $leadCategory): RedirectResponse|JsonResponse
    {
        if (! LeadCategorySchema::ready()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Lead categories are not available yet.'], 422);
            }

            return back()->withErrors(['name' => 'Lead categories are not available yet.']);
        }

        abort_unless($leadCategory->organization_id === OrganizationContext::idOrFail(), 404);

        if ($leadCategory->leads()->exists()) {
            $message = 'This category has leads and cannot be deleted.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $name = $leadCategory->name;
        $leadCategory->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Category “'.$name.'” deleted.']);
        }

        return redirect()
            ->route('admin.crm.leads.import.create')
            ->with('success', 'Category “'.$name.'” deleted.');
    }

    /** @return array<string, mixed> */
    private function categoryPayload(LeadCategory $category): array
    {
        $category->loadCount('leads');

        return [
            'id' => $category->id,
            'name' => $category->name,
            'icon' => $category->displayIcon(),
            'tone' => $category->displayTone(),
            'leads_count' => (int) ($category->leads_count ?? 0),
            'destroy_url' => route('admin.crm.leads.import.categories.destroy', $category),
        ];
    }

    public function map(LeadImport $leadImport): View|RedirectResponse
    {
        if (LeadCategorySchema::ready() && ! $leadImport->lead_category_id) {
            return redirect()->route('admin.crm.leads.import.category', $leadImport)
                ->with('error', 'Select a lead category before mapping columns.');
        }

        $data = $this->imports->mappingSuggestions($leadImport);
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();

        return view('admin.crm.leads.import.map', [
            'import' => $leadImport->load('category'),
            'parsed' => $data['parsed'],
            'suggestions' => $data['suggestions'],
            'fieldOptions' => LeadImportFields::options(),
            'admins' => $admins,
            'statuses' => LeadStatus::options(),
            'priorities' => LeadPriority::options(),
            'isAdministrationSheet' => app(AdministrationSheetImportProfile::class)
                ->matches($data['parsed']['headers'] ?? []),
        ]);
    }

    public function saveMap(MapLeadImportRequest $request, LeadImport $leadImport): RedirectResponse
    {
        if (LeadCategorySchema::ready() && ! $leadImport->lead_category_id) {
            return redirect()->route('admin.crm.leads.import.category', $leadImport)
                ->with('error', 'Select a lead category before mapping columns.');
        }

        $this->imports->saveMapping($leadImport, [
            'selected_sheet' => $request->input('selected_sheet'),
            'header_row' => $request->integer('header_row'),
            'mapping' => $request->input('mapping', []),
            'options' => $request->input('options', []),
        ]);

        return redirect()->route('admin.crm.leads.import.preview', $leadImport)
            ->with('success', 'Mapping saved. Review the preview before confirming.');
    }

    public function preview(LeadImport $leadImport): View
    {
        $leadImport->load('category');
        $rows = $leadImport->rows()->orderBy('row_number')->limit((int) config('lead_import.preview_rows', 50))->get();
        $headers = $leadImport->detected_headers ?? [];
        $mapping = $leadImport->mapping ?? [];
        $mapped = [];
        $unmapped = [];
        foreach ($headers as $header) {
            $field = $mapping[$header['key']] ?? 'custom';
            if ($field === 'custom') {
                $unmapped[] = $header['label'];
            } else {
                $mapped[] = $header['label'].' → '.(LeadImportFields::options()[$field] ?? $field);
            }
        }

        return view('admin.crm.leads.import.preview', [
            'import' => $leadImport,
            'rows' => $rows,
            'mapped' => $mapped,
            'unmapped' => $unmapped,
            'previousImportCount' => LeadImport::forCurrentOrganization()
                ->where('file_hash', $leadImport->file_hash)
                ->where('id', '!=', $leadImport->id)
                ->where('status', LeadImportStatus::Completed->value)
                ->count(),
        ]);
    }

    public function confirm(ConfirmLeadImportRequest $request, LeadImport $leadImport): RedirectResponse
    {
        $import = $this->imports->confirm($leadImport);

        return redirect()->route('admin.crm.leads.import.show', $import)
            ->with('success', $import->imported_rows.' lead(s) imported.');
    }

    public function undo(Request $request, LeadImport $leadImport): RedirectResponse|JsonResponse
    {
        try {
            $stats = $this->undoService->undo($leadImport, $request->user('admin'));
        } catch (RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        $message = $stats['undone'].' lead(s) removed from view.';
        if ($stats['skipped_converted'] > 0) {
            $message .= ' '.$stats['skipped_converted'].' converted lead(s) were kept.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'stats' => $stats,
            ]);
        }

        return redirect()
            ->route('admin.crm.leads.import.show', $leadImport->fresh())
            ->with('success', $message);
    }

    public function undoAll(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $stats = $this->undoService->undoAll($request->user('admin'));
        } catch (RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        $message = $stats['undone'].' imported lead(s) removed from view across '.$stats['batches'].' batch(es).';
        if ($stats['skipped_converted'] > 0) {
            $message .= ' '.$stats['skipped_converted'].' converted lead(s) were kept.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'stats' => $stats,
            ]);
        }

        return redirect()
            ->route('admin.crm.leads.import.index')
            ->with('success', $message);
    }

    public function show(LeadImport $leadImport): View
    {
        $leadImport->load(['uploader', 'category', 'undoneBy']);
        $rows = $leadImport->rows()->with('leadRecord')->orderBy('row_number')->paginate(50);

        return view('admin.crm.leads.import.show', [
            'import' => $leadImport,
            'rows' => $rows,
        ]);
    }

    public function failedRows(LeadImport $leadImport): BinaryFileResponse
    {
        $rows = $leadImport->rows()
            ->whereIn('status', [
                LeadImportRowStatus::Failed->value,
                LeadImportRowStatus::Invalid->value,
                LeadImportRowStatus::Skipped->value,
            ])
            ->orderBy('row_number')
            ->get();

        return Excel::download(
            new LeadImportFailedRowsExport($leadImport, $rows),
            'lead-import-'.$leadImport->id.'-issues.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}
