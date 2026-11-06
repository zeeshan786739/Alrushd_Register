@php
    $intake = request('admission_intake');
    $notSet = \App\Support\LeadFormOptions::FILTER_NOT_SET;
    $intakeIsTheReason = is_string($intake) && $intake !== '' && $intake !== $notSet;
@endphp
<div class="crm-leads-no-results" role="status">
    <span class="crm-leads-no-results__icon" aria-hidden="true"><iconify-icon icon="solar:filter-linear"></iconify-icon></span>
    <h3>No leads match these filters</h3>
    @if($intakeIsTheReason)
        <p>No leads match {{ $intake }} with these filters. Leads need an admission intake before they appear here: find the ones with no intake, tick them and use Set intake.</p>
    @else
        <p>Try removing a filter or widening the dates.</p>
    @endif
    <div class="crm-leads-no-results__actions">
        @if($intakeIsTheReason)
            <a href="{{ \App\Support\CrmLeadFilterUrl::for(['admission_intake' => $notSet, 'view' => $viewMode], ['admission_intake']) }}" class="crm-filter-workspace__btn crm-filter-workspace__btn--primary">Show leads with no intake</a>
        @endif
        <a href="{{ route('admin.crm.leads.index', ['view' => $viewMode]) }}" class="crm-filter-workspace__btn">Clear all filters</a>
    </div>
</div>
