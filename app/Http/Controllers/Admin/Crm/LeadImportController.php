<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\LeadImportRowStatus;
use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Exports\Crm\LeadImportFailedRowsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\ConfirmLeadImportRequest;
use App\Http\Requests\Crm\MapLeadImportRequest;
use App\Http\Requests\Crm\UploadLeadImportRequest;
use App\Models\Admin;
use App\Models\Crm\LeadImport;
use App\Services\Crm\LeadImport\LeadImportService;
use App\Support\LeadImportFields;
use Illuminate\Http\RedirectResponse;
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

    public function index(): View
    {
        $imports = LeadImport::forCurrentOrganization()
            ->with('uploader')
            ->withCount('leads')
            ->latest()
            ->paginate(20);

        return view('admin.crm.leads.import.index', compact('imports'));
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

        return redirect()->route('admin.crm.leads.import.map', $import)
            ->with('success', 'File uploaded. Review column mapping before importing.');
    }

    public function map(LeadImport $leadImport): View
    {
        $data = $this->imports->mappingSuggestions($leadImport);
        $admins = Admin::forCurrentOrganization()->orderBy('name')->get();

        return view('admin.crm.leads.import.map', [
            'import' => $leadImport,
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
        $rows = $leadImport->rows()->orderBy('row_number')->limit((int) config('lead_import.preview_rows', 50))->get();
        $headers = $leadImport->detected_headers ?? [];
        $mapping = $leadImport->mapping ?? [];
        $unmapped = [];
        foreach ($headers as $header) {
            $field = $mapping[$header['key']] ?? 'custom';
            if ($field === 'custom') {
                $unmapped[] = $header['label'];
            }
        }

        return view('admin.crm.leads.import.preview', [
            'import' => $leadImport,
            'rows' => $rows,
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
        $leadImport->load('uploader');
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
