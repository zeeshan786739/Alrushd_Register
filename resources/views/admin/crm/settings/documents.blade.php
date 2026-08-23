@extends('admin.layouts.app')
@section('title', 'Document Settings')
@section('content')
@include('admin.crm.partials.styles')
@php
    $b = $settings['branding'];
    $q = $settings['quotation'];
    $i = $settings['invoice'];
@endphp
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Document Settings',
        'subtitle' => 'Configure Quotation and Invoice branding and visibility for this organization only',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label'=>'CRM'],['label'=>'Document Settings']],
    ])

    <div class="alert alert-primary bg-primary-50 text-primary-600 border-0 radius-8 mb-20">
        Fields appear on documents only when both a value exists and its visibility toggle is enabled. Organization profile data is never shown automatically.
    </div>

    <ul class="nav nav-pills gap-8 mb-20">
        <li class="nav-item"><a class="nav-link radius-8 {{ $tab === 'branding' ? 'active' : '' }}" href="{{ route('admin.crm.settings.documents.edit', ['tab'=>'branding']) }}">Shared Branding</a></li>
        <li class="nav-item"><a class="nav-link radius-8 {{ $tab === 'quotation' ? 'active' : '' }}" href="{{ route('admin.crm.settings.documents.edit', ['tab'=>'quotation']) }}">Quotation</a></li>
        <li class="nav-item"><a class="nav-link radius-8 {{ $tab === 'invoice' ? 'active' : '' }}" href="{{ route('admin.crm.settings.documents.edit', ['tab'=>'invoice']) }}">Invoice</a></li>
    </ul>

    <form method="POST" action="{{ route('admin.crm.settings.documents.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="active_tab" value="{{ $tab }}">

        <div class="card radius-12 shadow-2 border-0 mb-20 {{ $tab === 'branding' ? '' : 'd-none' }}">
            <div class="card-body p-24">
                <h6 class="crm-section-title">Shared branding</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Document logo</label>
                        @php $currentLogo = \App\Support\CrmDocument::logoForPreview($settings['logo_path'] ?? null, (int) $settings['organization_id']); @endphp
                        @if($currentLogo)
                            <div class="border radius-8 p-12 mb-12 bg-neutral-50">
                                <div class="crm-lead-meta mb-8">Current logo</div>
                                <img src="{{ $currentLogo }}" alt="Current document logo" style="max-height:64px;max-width:180px;object-fit:contain;display:block">
                            </div>
                            <label class="form-label text-sm">Replace logo</label>
                        @else
                            <div class="crm-lead-meta mb-8">No logo uploaded</div>
                        @endif
                        <input type="file" name="logo" class="form-control radius-8" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                        @error('logo')<div class="text-danger-600 text-sm mt-4">{{ $message }}</div>@enderror
                        @if($currentLogo)
                            <label class="form-check mt-8 mb-0">
                                <input type="checkbox" class="form-check-input" name="remove_logo" value="1">
                                Remove logo
                            </label>
                        @endif
                        <label class="form-check mt-8">
                            <input type="checkbox" class="form-check-input" name="branding[show_logo]" value="1" @checked($b['show_logo'])>
                            Show logo on quotation/invoice documents
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Display name</label>
                        <input type="text" name="branding[display_name]" class="form-control radius-8" value="{{ old('branding.display_name', $b['display_name']) }}">
                        <label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="branding[show_display_name]" value="1" @checked($b['show_display_name'])> Show organization name</label>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="branding[address]" class="form-control radius-8" rows="2">{{ old('branding.address', $b['address']) }}</textarea>
                        <label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="branding[show_address]" value="1" @checked($b['show_address'])> Show address</label>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="branding[email]" class="form-control radius-8" value="{{ old('branding.email', $b['email']) }}">
                        <label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="branding[show_email]" value="1" @checked($b['show_email'])> Show email</label>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="branding[phone]" class="form-control radius-8" value="{{ old('branding.phone', $b['phone']) }}">
                        <label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="branding[show_phone]" value="1" @checked($b['show_phone'])> Show phone</label>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Website</label>
                        <input type="text" name="branding[website]" class="form-control radius-8" value="{{ old('branding.website', $b['website']) }}">
                        <label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="branding[show_website]" value="1" @checked($b['show_website'])> Show website</label>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Registration / company number</label>
                        <input type="text" name="branding[registration_number]" class="form-control radius-8" value="{{ old('branding.registration_number', $b['registration_number']) }}">
                        <label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="branding[show_registration_number]" value="1" @checked($b['show_registration_number'])> Show registration number</label>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">VAT / tax number</label>
                        <input type="text" name="branding[vat_number]" class="form-control radius-8" value="{{ old('branding.vat_number', $b['vat_number']) }}">
                        <label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="branding[show_vat_number]" value="1" @checked($b['show_vat_number'])> Show VAT/tax number</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card radius-12 shadow-2 border-0 mb-20 {{ $tab === 'quotation' ? '' : 'd-none' }}">
            <div class="card-body p-24">
                <h6 class="crm-section-title">Quotation document</h6>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Heading</label><input type="text" name="quotation[heading]" class="form-control radius-8" value="{{ old('quotation.heading', $q['heading']) }}"></div>
                    <div class="col-md-6"><label class="form-label">Subtitle</label><input type="text" name="quotation[subtitle]" class="form-control radius-8" value="{{ old('quotation.subtitle', $q['subtitle']) }}"></div>
                    <div class="col-12"><div class="crm-lead-meta mb-8">Visibility</div>
                        <div class="row g-2">
                            @foreach([
                                'show_customer_email'=>'Customer email','show_customer_phone'=>'Customer phone','show_project'=>'Project',
                                'show_status'=>'Status','show_issue_date'=>'Issue date','show_valid_until'=>'Valid until',
                                'show_subtotal'=>'Subtotal','show_discount'=>'Discount','show_tax'=>'Tax',
                                'show_terms'=>'Terms section','show_notes'=>'Notes section',
                            ] as $key=>$label)
                                <div class="col-md-4"><label class="form-check"><input type="checkbox" class="form-check-input" name="quotation[{{ $key }}]" value="1" @checked($q[$key])> {{ $label }}</label></div>
                            @endforeach
                        </div>
                        <p class="crm-lead-meta mt-8 mb-0">Line items and Grand Total always remain visible.</p>
                    </div>
                    <div class="col-12"><label class="form-label">Default terms text</label><textarea name="quotation[terms_text]" class="form-control radius-8" rows="3">{{ old('quotation.terms_text', $q['terms_text']) }}</textarea></div>
                    <div class="col-12"><label class="form-label">Footer text</label><textarea name="quotation[footer_text]" class="form-control radius-8" rows="2">{{ old('quotation.footer_text', $q['footer_text']) }}</textarea></div>
                </div>
            </div>
        </div>

        <div class="card radius-12 shadow-2 border-0 mb-20 {{ $tab === 'invoice' ? '' : 'd-none' }}">
            <div class="card-body p-24">
                <h6 class="crm-section-title">Invoice document</h6>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Heading</label><input type="text" name="invoice[heading]" class="form-control radius-8" value="{{ old('invoice.heading', $i['heading']) }}"></div>
                    <div class="col-md-6"><label class="form-label">Subtitle</label><input type="text" name="invoice[subtitle]" class="form-control radius-8" value="{{ old('invoice.subtitle', $i['subtitle']) }}"></div>
                    <div class="col-12"><div class="crm-lead-meta mb-8">Visibility</div>
                        <div class="row g-2">
                            @foreach([
                                'show_customer_email'=>'Customer email','show_customer_phone'=>'Customer phone','show_project'=>'Project',
                                'show_source_quotation'=>'Source quotation','show_status'=>'Status','show_issue_date'=>'Issue date','show_due_date'=>'Due date',
                                'show_subtotal'=>'Subtotal','show_discount'=>'Discount','show_tax'=>'Tax','show_total'=>'Total',
                                'show_amount_paid'=>'Amount paid','show_balance_due'=>'Balance due','show_payment_history'=>'Payment history','show_terms'=>'Terms section',
                            ] as $key=>$label)
                                <div class="col-md-4"><label class="form-check"><input type="checkbox" class="form-check-input" name="invoice[{{ $key }}]" value="1" @checked($i[$key])> {{ $label }}</label></div>
                            @endforeach
                        </div>
                        <p class="crm-lead-meta mt-8 mb-0">Line items always remain visible.</p>
                    </div>
                    <div class="col-12"><hr class="my-8"><div class="crm-lead-meta mb-8">Optional payment information (hidden unless enabled)</div></div>
                    <div class="col-md-6"><label class="form-label">Bank name</label><input type="text" name="invoice[bank_name]" class="form-control radius-8" value="{{ old('invoice.bank_name', $i['bank_name']) }}"><label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="invoice[show_bank_name]" value="1" @checked($i['show_bank_name'])> Show bank name</label></div>
                    <div class="col-md-6"><label class="form-label">Account name</label><input type="text" name="invoice[account_name]" class="form-control radius-8" value="{{ old('invoice.account_name', $i['account_name']) }}"><label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="invoice[show_account_name]" value="1" @checked($i['show_account_name'])> Show account name</label></div>
                    <div class="col-md-6"><label class="form-label">Account / IBAN</label><input type="text" name="invoice[account_number]" class="form-control radius-8" value="{{ old('invoice.account_number', $i['account_number']) }}"><label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="invoice[show_account_number]" value="1" @checked($i['show_account_number'])> Show account / IBAN</label></div>
                    <div class="col-md-6"><label class="form-label">Sort code / SWIFT</label><input type="text" name="invoice[sort_code]" class="form-control radius-8" value="{{ old('invoice.sort_code', $i['sort_code']) }}"><label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="invoice[show_sort_code]" value="1" @checked($i['show_sort_code'])> Show sort code / SWIFT</label></div>
                    <div class="col-12"><label class="form-label">Other payment instructions</label><textarea name="invoice[payment_instructions]" class="form-control radius-8" rows="2">{{ old('invoice.payment_instructions', $i['payment_instructions']) }}</textarea><label class="form-check mt-8"><input type="checkbox" class="form-check-input" name="invoice[show_payment_instructions]" value="1" @checked($i['show_payment_instructions'])> Show payment instructions</label></div>
                    <div class="col-12"><label class="form-label">Default terms text</label><textarea name="invoice[terms_text]" class="form-control radius-8" rows="3">{{ old('invoice.terms_text', $i['terms_text']) }}</textarea></div>
                    <div class="col-12"><label class="form-label">Footer text</label><textarea name="invoice[footer_text]" class="form-control radius-8" rows="2">{{ old('invoice.footer_text', $i['footer_text']) }}</textarea></div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11">Save document settings</button>
    </form>
</div>
@endsection
