<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\LeadImportRowStatus;
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
use App\Support\LeadCategorySchema;
use App\Support\LeadImportFields;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeadImportController extends Controller
{
    public function __construct(private LeadImportService $imports)
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

        return view('admin.crm.leads.import.index', compact('imports', 'categories'));
    }

    public function create(): View
    {
        return view('admin.crm.leads.import.create');
    }

    public function store(UploadLeadImportRequest $request): RedirectResponse
    {
        try {
            $import = $this->imports->createFromUpload($request->file('file'), $request->user('admin'));
        } catch (RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        if (LeadCategorySchema::ready()) {
            return redirect()->route('admin.crm.leads.import.category', $import)
                ->with('success', 'File uploaded. Select a lead category before mapping columns.');
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
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.crm.leads.import.category', [
            'import' => $leadImport,
            'categories' => $categories,
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

    public function storeCategory(StoreLeadCategoryRequest $request, ?LeadImport $leadImport = null): RedirectResponse
    {
        if (! LeadCategorySchema::ready()) {
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

            return redirect()->route('admin.crm.leads.import.map', $leadImport)
                ->with('success', 'Category created and selected. Review column mapping before importing.');
        }

        return back()->with('success', 'Lead category created.');
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
                ->where('status', 'completed')
                ->count(),
        ]);
    }

    public function confirm(ConfirmLeadImportRequest $request, LeadImport $leadImport): RedirectResponse
    {
        $import = $this->imports->confirm($leadImport);

        return redirect()->route('admin.crm.leads.import.show', $import)
            ->with('success', $import->imported_rows.' lead(s) imported.');
    }

    public function show(LeadImport $leadImport): View
    {
        $leadImport->load(['uploader', 'category']);
        $rows = $leadImport->rows()->with('lead')->orderBy('row_number')->paginate(50);

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
