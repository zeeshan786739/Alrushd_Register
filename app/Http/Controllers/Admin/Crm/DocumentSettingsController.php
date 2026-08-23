<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\UpdateDocumentSettingsRequest;
use App\Models\Crm\DocumentSetting;
use App\Support\CrmDocument;
use App\Support\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage crm documents');
    }

    public function edit(Request $request): View
    {
        $organizationId = OrganizationContext::idOrFail();
        $settings = CrmDocument::settings($organizationId);
        $tab = in_array($request->get('tab'), ['branding', 'quotation', 'invoice'], true)
            ? $request->get('tab')
            : 'branding';

        return view('admin.crm.settings.documents', compact('settings', 'tab'));
    }

    public function update(UpdateDocumentSettingsRequest $request): RedirectResponse
    {
        $organizationId = OrganizationContext::idOrFail();
        $tab = in_array($request->input('active_tab'), ['branding', 'quotation', 'invoice'], true)
            ? $request->input('active_tab')
            : 'branding';

        $row = DocumentSetting::query()->firstOrNew(['organization_id' => $organizationId]);
        $current = CrmDocument::settings($organizationId);

        $row->branding = $row->branding ?? $current['branding'];
        $row->quotation = $row->quotation ?? $current['quotation'];
        $row->invoice = $row->invoice ?? $current['invoice'];

        if ($tab === 'branding') {
            $row->branding = CrmDocument::mergeDefaults(
                CrmDocument::defaultBranding(),
                $request->validated('branding') ?? []
            );

            if ($request->boolean('remove_logo') && $row->logo_path) {
                Storage::disk('public')->delete($row->logo_path);
                $row->logo_path = null;
            }

            if ($request->hasFile('logo')) {
                if ($row->logo_path) {
                    Storage::disk('public')->delete($row->logo_path);
                }
                $row->logo_path = $request->file('logo')->store(
                    'crm-documents/'.$organizationId,
                    'public'
                );
            }
        } elseif ($tab === 'quotation') {
            $row->quotation = CrmDocument::mergeDefaults(
                CrmDocument::defaultQuotation(),
                $request->validated('quotation') ?? []
            );
        } else {
            $row->invoice = CrmDocument::mergeDefaults(
                CrmDocument::defaultInvoice(),
                $request->validated('invoice') ?? []
            );
        }

        $row->organization_id = $organizationId;
        $row->save();

        return redirect()
            ->route('admin.crm.settings.documents.edit', ['tab' => $tab])
            ->with('success', 'Document settings saved for this organization.');
    }
}
