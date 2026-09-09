@extends('admin.layouts.app')
@section('title', 'Document Settings')
@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
@include('admin.crm.settings.partials.premium-styles')
@php
    $b = $settings['branding'];
    $q = $settings['quotation'];
    $i = $settings['invoice'];
    $currentLogo = \App\Support\CrmDocument::logoForPreview($settings['logo_path'] ?? null, (int) $settings['organization_id']);
    $tabs = [
        ['key' => 'branding', 'label' => 'Shared Branding', 'icon' => 'solar:palette-linear', 'sub' => 'Logo, identity, and contact details'],
        ['key' => 'quotation', 'label' => 'Quotation', 'icon' => 'solar:document-linear', 'sub' => 'Headings, visibility, and terms'],
        ['key' => 'invoice', 'label' => 'Invoice', 'icon' => 'solar:bill-list-linear', 'sub' => 'Billing fields, payments, and terms'],
    ];
@endphp
<div class="dashboard-main-body" id="crm-document-settings-page">
    @include('admin.partials.page-header', [
        'title' => 'Document Settings',
        'subtitle' => 'Configure quotation and invoice branding and visibility for this organization',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'CRM'], ['label' => 'Document Settings']],
    ])

    <div class="crm-doc-workspace crm-workspace-shell">
        <div class="crm-metrics-strip" aria-label="Document workspace links">
            <div class="crm-metrics-strip__items">
                <span class="crm-metrics-strip__hint">Document workspace</span>
            </div>
            <div class="crm-metrics-strip__links">
                @can('view quotations')
                    <a href="{{ route('admin.crm.quotations.index') }}" class="crm-metrics-strip__link">Quotations</a>
                @endcan
                @can('view invoices')
                    <a href="{{ route('admin.crm.invoices.index') }}" class="crm-metrics-strip__link">Invoices</a>
                @endcan
            </div>
        </div>

        <div class="crm-doc-notice" role="note">
            <iconify-icon icon="solar:info-circle-linear"></iconify-icon>
            <span>Fields appear on documents only when both a value exists and its visibility toggle is enabled. Organization profile data is never shown automatically.</span>
        </div>

        <div class="crm-doc-tabs" aria-label="Document settings sections">
            <div class="crm-doc-tabs__head">
                <h2 class="crm-doc-tabs__title">Configuration area</h2>
                <p class="crm-doc-tabs__sub">Choose what to customize for PDF and preview documents</p>
            </div>
            <div class="crm-doc-tabs__grid">
                @foreach($tabs as $item)
                    <a href="{{ route('admin.crm.settings.documents.edit', ['tab' => $item['key']]) }}"
                       @class(['crm-doc-tab', 'is-active' => $tab === $item['key']])>
                        <span class="crm-doc-tab__icon"><iconify-icon icon="{{ $item['icon'] }}"></iconify-icon></span>
                        <span class="crm-doc-tab__label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('admin.crm.settings.documents.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" value="{{ $tab }}">

            <div class="crm-doc-panel {{ $tab === 'branding' ? '' : 'd-none' }}">
                <section class="crm-doc-section">
                    <div class="crm-doc-section__head">
                        <h3 class="crm-doc-section__title">Shared branding</h3>
                        <p class="crm-doc-section__sub">Identity and contact details shown on quotation and invoice documents</p>
                    </div>
                    <div class="crm-doc-section__body">
                        <div class="crm-doc-grid">
                            <div class="crm-doc-grid__col-6">
                                <div class="crm-doc-field">
                                    <div class="crm-doc-field__head">
                                        <span class="crm-doc-field__label">Document logo</span>
                                        <label class="crm-doc-toggle" for="crm-doc-show-logo">
                                            <input type="checkbox" class="crm-doc-toggle__input" id="crm-doc-show-logo" name="branding[show_logo]" value="1" @checked($b['show_logo'])>
                                            <span class="crm-doc-toggle__track" aria-hidden="true"></span>
                                            <span class="crm-doc-toggle__text">Show on documents</span>
                                        </label>
                                    </div>
                                    <div class="crm-doc-logo-zone">
                                        <div class="crm-doc-logo-zone__preview">
                                            @if($currentLogo)
                                                <img src="{{ $currentLogo }}" alt="Current document logo">
                                            @else
                                                <span class="crm-doc-logo-zone__empty">No logo uploaded yet</span>
                                            @endif
                                        </div>
                                        <div class="crm-doc-logo-zone__file">
                                            <input type="file" name="logo" class="form-control" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                                        </div>
                                        @error('logo')<div class="text-danger-600">{{ $message }}</div>@enderror
                                        @if($currentLogo)
                                            <label class="crm-doc-check">
                                                <input type="checkbox" class="form-check-input" name="remove_logo" value="1">
                                                Remove current logo
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Display name',
                                    'name' => 'branding[display_name]',
                                    'value' => old('branding.display_name', $b['display_name']),
                                    'toggleName' => 'branding[show_display_name]',
                                    'toggleLabel' => 'Show name',
                                    'toggleChecked' => $b['show_display_name'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-12">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Address',
                                    'name' => 'branding[address]',
                                    'value' => old('branding.address', $b['address']),
                                    'type' => 'textarea',
                                    'rows' => 2,
                                    'toggleName' => 'branding[show_address]',
                                    'toggleLabel' => 'Show address',
                                    'toggleChecked' => $b['show_address'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-4">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Email',
                                    'name' => 'branding[email]',
                                    'value' => old('branding.email', $b['email']),
                                    'type' => 'email',
                                    'toggleName' => 'branding[show_email]',
                                    'toggleLabel' => 'Show email',
                                    'toggleChecked' => $b['show_email'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-4">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Phone',
                                    'name' => 'branding[phone]',
                                    'value' => old('branding.phone', $b['phone']),
                                    'toggleName' => 'branding[show_phone]',
                                    'toggleLabel' => 'Show phone',
                                    'toggleChecked' => $b['show_phone'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-4">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Website',
                                    'name' => 'branding[website]',
                                    'value' => old('branding.website', $b['website']),
                                    'toggleName' => 'branding[show_website]',
                                    'toggleLabel' => 'Show website',
                                    'toggleChecked' => $b['show_website'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Registration / company number',
                                    'name' => 'branding[registration_number]',
                                    'value' => old('branding.registration_number', $b['registration_number']),
                                    'toggleName' => 'branding[show_registration_number]',
                                    'toggleLabel' => 'Show registration',
                                    'toggleChecked' => $b['show_registration_number'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'VAT / tax number',
                                    'name' => 'branding[vat_number]',
                                    'value' => old('branding.vat_number', $b['vat_number']),
                                    'toggleName' => 'branding[show_vat_number]',
                                    'toggleLabel' => 'Show VAT/tax',
                                    'toggleChecked' => $b['show_vat_number'],
                                ])
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="crm-doc-panel {{ $tab === 'quotation' ? '' : 'd-none' }}">
                <section class="crm-doc-section">
                    <div class="crm-doc-section__head">
                        <h3 class="crm-doc-section__title">Quotation document</h3>
                        <p class="crm-doc-section__sub">Control headings, field visibility, and default copy</p>
                    </div>
                    <div class="crm-doc-section__body">
                        <div class="crm-doc-grid">
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Heading',
                                    'name' => 'quotation[heading]',
                                    'value' => old('quotation.heading', $q['heading']),
                                ])
                            </div>
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Subtitle',
                                    'name' => 'quotation[subtitle]',
                                    'value' => old('quotation.subtitle', $q['subtitle']),
                                ])
                            </div>
                            <div class="crm-doc-grid__col-12">
                                <span class="crm-doc-field__label">Field visibility</span>
                                @include('admin.crm.settings.partials.visibility-grid', [
                                    'prefix' => 'quotation',
                                    'values' => $q,
                                    'fields' => [
                                        'show_customer_email' => 'Customer email',
                                        'show_customer_phone' => 'Customer phone',
                                        'show_project' => 'Project',
                                        'show_status' => 'Status',
                                        'show_issue_date' => 'Issue date',
                                        'show_valid_until' => 'Valid until',
                                        'show_subtotal' => 'Subtotal',
                                        'show_discount' => 'Discount',
                                        'show_tax' => 'Tax',
                                        'show_terms' => 'Terms section',
                                        'show_notes' => 'Notes section',
                                    ],
                                ])
                                <p class="crm-doc-footnote">Line items and Grand Total always remain visible.</p>
                            </div>
                            <div class="crm-doc-grid__col-12">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Default terms text',
                                    'name' => 'quotation[terms_text]',
                                    'value' => old('quotation.terms_text', $q['terms_text']),
                                    'type' => 'textarea',
                                    'rows' => 3,
                                ])
                            </div>
                            <div class="crm-doc-grid__col-12">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Footer text',
                                    'name' => 'quotation[footer_text]',
                                    'value' => old('quotation.footer_text', $q['footer_text']),
                                    'type' => 'textarea',
                                    'rows' => 2,
                                ])
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="crm-doc-panel {{ $tab === 'invoice' ? '' : 'd-none' }}">
                <section class="crm-doc-section">
                    <div class="crm-doc-section__head">
                        <h3 class="crm-doc-section__title">Invoice document</h3>
                        <p class="crm-doc-section__sub">Billing layout, payment details, and default copy</p>
                    </div>
                    <div class="crm-doc-section__body">
                        <div class="crm-doc-grid">
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Heading',
                                    'name' => 'invoice[heading]',
                                    'value' => old('invoice.heading', $i['heading']),
                                ])
                            </div>
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Subtitle',
                                    'name' => 'invoice[subtitle]',
                                    'value' => old('invoice.subtitle', $i['subtitle']),
                                ])
                            </div>
                            <div class="crm-doc-grid__col-12">
                                <span class="crm-doc-field__label">Field visibility</span>
                                @include('admin.crm.settings.partials.visibility-grid', [
                                    'prefix' => 'invoice',
                                    'values' => $i,
                                    'fields' => [
                                        'show_customer_email' => 'Customer email',
                                        'show_customer_phone' => 'Customer phone',
                                        'show_project' => 'Project',
                                        'show_source_quotation' => 'Source quotation',
                                        'show_status' => 'Status',
                                        'show_issue_date' => 'Issue date',
                                        'show_due_date' => 'Due date',
                                        'show_subtotal' => 'Subtotal',
                                        'show_discount' => 'Discount',
                                        'show_tax' => 'Tax',
                                        'show_total' => 'Total',
                                        'show_amount_paid' => 'Amount paid',
                                        'show_balance_due' => 'Balance due',
                                        'show_payment_history' => 'Payment history',
                                        'show_terms' => 'Terms section',
                                    ],
                                ])
                                <p class="crm-doc-footnote">Line items always remain visible.</p>
                            </div>
                            <div class="crm-doc-grid__col-12"><div class="crm-doc-divider"></div></div>
                            <div class="crm-doc-grid__col-12">
                                <span class="crm-doc-field__label">Optional payment information</span>
                                <p class="crm-doc-footnote" style="margin-top:0;margin-bottom:10px">Hidden on documents unless both a value exists and its toggle is enabled.</p>
                            </div>
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Bank name',
                                    'name' => 'invoice[bank_name]',
                                    'value' => old('invoice.bank_name', $i['bank_name']),
                                    'toggleName' => 'invoice[show_bank_name]',
                                    'toggleLabel' => 'Show bank',
                                    'toggleChecked' => $i['show_bank_name'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Account name',
                                    'name' => 'invoice[account_name]',
                                    'value' => old('invoice.account_name', $i['account_name']),
                                    'toggleName' => 'invoice[show_account_name]',
                                    'toggleLabel' => 'Show account',
                                    'toggleChecked' => $i['show_account_name'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Account / IBAN',
                                    'name' => 'invoice[account_number]',
                                    'value' => old('invoice.account_number', $i['account_number']),
                                    'toggleName' => 'invoice[show_account_number]',
                                    'toggleLabel' => 'Show IBAN',
                                    'toggleChecked' => $i['show_account_number'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-6">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Sort code / SWIFT',
                                    'name' => 'invoice[sort_code]',
                                    'value' => old('invoice.sort_code', $i['sort_code']),
                                    'toggleName' => 'invoice[show_sort_code]',
                                    'toggleLabel' => 'Show SWIFT',
                                    'toggleChecked' => $i['show_sort_code'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-12">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Other payment instructions',
                                    'name' => 'invoice[payment_instructions]',
                                    'value' => old('invoice.payment_instructions', $i['payment_instructions']),
                                    'type' => 'textarea',
                                    'rows' => 2,
                                    'toggleName' => 'invoice[show_payment_instructions]',
                                    'toggleLabel' => 'Show instructions',
                                    'toggleChecked' => $i['show_payment_instructions'],
                                ])
                            </div>
                            <div class="crm-doc-grid__col-12">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Default terms text',
                                    'name' => 'invoice[terms_text]',
                                    'value' => old('invoice.terms_text', $i['terms_text']),
                                    'type' => 'textarea',
                                    'rows' => 3,
                                ])
                            </div>
                            <div class="crm-doc-grid__col-12">
                                @include('admin.crm.settings.partials.field-with-toggle', [
                                    'label' => 'Footer text',
                                    'name' => 'invoice[footer_text]',
                                    'value' => old('invoice.footer_text', $i['footer_text']),
                                    'type' => 'textarea',
                                    'rows' => 2,
                                ])
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="crm-doc-savebar">
                <span class="crm-doc-savebar__hint">Changes apply to this organization only and affect PDF and preview documents.</span>
                <div class="crm-doc-savebar__actions">
                    <button type="submit" class="btn btn-primary-600">
                        <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                        Save document settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
