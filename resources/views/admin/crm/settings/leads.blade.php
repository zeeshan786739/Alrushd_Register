@extends('admin.layouts.app')
@section('title', 'Lead Settings')
@section('content')
@include('admin.crm.partials.styles')
@include('admin.crm.partials.workspace-shell')
<link rel="stylesheet" href="{{ \App\Support\AdminAsset::url('admin/assets/css/crm-lead-settings.css') }}">
<div class="dashboard-main-body" id="crm-lead-settings-page">
    @include('admin.partials.page-header', [
        'title' => 'Lead Settings',
        'subtitle' => 'Choose the filters, quick buttons and dropdown options your team sees on the Leads page',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'CRM'], ['label' => 'Leads', 'url' => route('admin.crm.leads.index')], ['label' => 'Lead Settings']],
    ])

    <div class="cls" data-cls-root data-url="{{ route('admin.crm.settings.leads.edit') }}" data-state="{{ json_encode($state) }}">
        <div class="cls-top">
            <div class="cls-tabs" role="tablist" aria-label="Lead settings sections">
                <button type="button" class="cls-tab" role="tab" data-cls-tab="filters"><iconify-icon icon="solar:filter-linear" aria-hidden="true"></iconify-icon><span>Filters</span></button>
                <button type="button" class="cls-tab" role="tab" data-cls-tab="quick"><iconify-icon icon="solar:bolt-linear" aria-hidden="true"></iconify-icon><span>Quick buttons</span></button>
                <button type="button" class="cls-tab" role="tab" data-cls-tab="options"><iconify-icon icon="solar:list-check-linear" aria-hidden="true"></iconify-icon><span>Dropdown options</span></button>
            </div>
            <div class="cls-top__end">
                <span class="cls-saved" data-cls-saved role="status" aria-live="polite"><iconify-icon icon="solar:check-circle-linear" aria-hidden="true"></iconify-icon><span>Changes save automatically</span></span>
                <a href="{{ route('admin.crm.leads.index') }}" class="cls-btn cls-btn--ghost">View Leads page <iconify-icon icon="solar:arrow-right-linear" aria-hidden="true"></iconify-icon></a>
            </div>
        </div>
        <div data-cls-panel></div>
        <div class="cls-toasts" data-cls-toasts aria-live="polite"></div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ \App\Support\AdminAsset::url('admin/assets/js/crm-lead-settings.js') }}"></script>
@endsection
