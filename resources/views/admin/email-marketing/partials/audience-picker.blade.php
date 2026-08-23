@php
    use App\Support\LeadSourceOptions;
    $filters = $filters ?? [];
    $currentSource = $currentSource ?? 'leads';
    $selectedLeadIds = old('lead_ids', $filters['lead_ids'] ?? []);
    $selectedCustomerIds = old('customer_ids', $filters['customer_ids'] ?? []);
    $selectedStatuses = old('lead_statuses', $filters['lead_statuses'] ?? []);
    if ($selectedStatuses === [] && ! empty($filters['lead_status'])) {
        $selectedStatuses = [$filters['lead_status']];
    }
    $selectedPriority = old('lead_priority', $filters['lead_priority'] ?? '');
    $leadStatuses = $leadStatuses ?? \App\Enums\LeadStatus::options();
    $leadPriorities = $leadPriorities ?? \App\Enums\LeadPriority::options();
@endphp

<div class="em-audience-builder" data-audience-picker>
    <input type="hidden" name="recipient_source" value="{{ $currentSource }}" data-audience-source>

    <div class="em-audience-builder__section">
        <div class="em-audience-builder__label">
            <strong>1. Choose audience type</strong>
            <span>Where should we pull contacts from?</span>
        </div>
        <div class="em-audience-sources">
            @foreach([
                ['key' => 'leads', 'icon' => 'solar:user-hand-up-linear', 'label' => 'All CRM leads', 'desc' => 'Pipeline contacts with email'],
                ['key' => 'customers', 'icon' => 'solar:users-group-rounded-linear', 'label' => 'All customers', 'desc' => 'Enrolled families & accounts'],
                ['key' => 'form_entries', 'icon' => 'solar:inbox-in-linear', 'label' => 'Form Center', 'desc' => 'Dynamic form submissions'],
                ['key' => 'integration_leads', 'icon' => 'solar:plug-circle-linear', 'label' => 'Integrations & imports', 'desc' => 'Facebook, TikTok, CSV'],
                ['key' => 'selected_leads', 'icon' => 'solar:checklist-minimalistic-linear', 'label' => 'Pick contacts', 'desc' => 'Search & hand-pick people'],
                ['key' => 'manual', 'icon' => 'solar:clipboard-text-linear', 'label' => 'Paste emails', 'desc' => 'Custom list of addresses'],
            ] as $source)
            <button type="button"
                    class="em-audience-source {{ $currentSource === $source['key'] ? 'is-active' : '' }}"
                    data-audience-source-option="{{ $source['key'] }}">
                <iconify-icon icon="{{ $source['icon'] }}"></iconify-icon>
                <span>
                    <strong>{{ $source['label'] }}</strong>
                    <small>{{ $source['desc'] }}</small>
                </span>
            </button>
            @endforeach
        </div>
    </div>

    <div class="em-audience-builder__section" data-audience-panel="leads customers integration_leads" @if(! in_array($currentSource, ['leads', 'customers', 'integration_leads'], true)) hidden @endif>
        <div class="em-audience-builder__label">
            <strong>2. Refine with filters</strong>
            <span>Narrow down who receives this campaign.</span>
        </div>

        <div class="em-filter-block" data-audience-lead-filters @if($currentSource === 'customers') hidden @endif>
            <span class="em-filter-block__title">Lead status</span>
            <div class="em-filter-pills" data-filter-group="lead_statuses">
                @foreach($leadStatuses as $value => $label)
                <label class="em-filter-pill">
                    <input type="checkbox" name="lead_statuses[]" value="{{ $value }}" @checked(in_array($value, $selectedStatuses, true)) data-audience-input>
                    <span>{{ $label }}</span>
                </label>
                @endforeach
            </div>
            <div class="form-text">Leave empty to include all statuses.</div>
        </div>

        <div class="em-filter-block" data-audience-lead-filters @if($currentSource === 'customers') hidden @endif>
            <span class="em-filter-block__title">Priority</span>
            <div class="em-filter-pills em-filter-pills--single" data-filter-group="lead_priority">
                <label class="em-filter-pill">
                    <input type="radio" name="lead_priority" value="" @checked($selectedPriority === '') data-audience-input>
                    <span>Any priority</span>
                </label>
                @foreach($leadPriorities as $value => $label)
                <label class="em-filter-pill">
                    <input type="radio" name="lead_priority" value="{{ $value }}" @checked($selectedPriority === $value) data-audience-input>
                    <span>{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="em-filter-block" data-audience-integration-filter @if($currentSource !== 'integration_leads') hidden @endif>
            <span class="em-filter-block__title">Integration source</span>
            <div class="em-filter-pills em-filter-pills--single">
                <label class="em-filter-pill">
                    <input type="radio" name="lead_source" value="" @checked(old('lead_source', $filters['lead_source'] ?? '') === '') data-audience-input>
                    <span>All sources</span>
                </label>
                @foreach(LeadSourceOptions::filterOptions() as $value => $label)
                <label class="em-filter-pill">
                    <input type="radio" name="lead_source" value="{{ $value }}" @checked(old('lead_source', $filters['lead_source'] ?? '') === $value) data-audience-input>
                    <span>{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="em-audience-builder__section" data-audience-panel="form_entries" @if($currentSource !== 'form_entries') hidden @endif>
        <div class="em-audience-builder__label">
            <strong>2. Form filter</strong>
            <span>Optionally limit to one form.</span>
        </div>
        <label class="form-label fw-semibold text-sm" for="form_id">Form</label>
        <select id="form_id" name="form_id" class="form-select radius-8" data-audience-form-select data-audience-input data-selected-form-id="{{ old('form_id', $filters['form_id'] ?? '') }}">
            <option value="">All form submissions</option>
        </select>
    </div>

    <div class="em-audience-builder__section" data-audience-panel="selected_leads" @if($currentSource !== 'selected_leads') hidden @endif>
        <div class="em-audience-builder__label">
            <strong>2. Search & select</strong>
            <span>Find leads or customers by name or email.</span>
        </div>
        <div class="um-search-bar um-search-bar--wide mb-12">
            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
            <input type="search" class="form-control radius-8" placeholder="Type at least 2 characters…" data-audience-search>
        </div>
        <div class="em-audience-search-results" data-audience-search-results></div>
        <div class="em-selected-contacts mt-12" data-audience-selected>
            @foreach($selectedLeadIds as $id)
                <input type="hidden" name="lead_ids[]" value="{{ $id }}" data-lead-id="{{ $id }}">
            @endforeach
            @foreach($selectedCustomerIds as $id)
                <input type="hidden" name="customer_ids[]" value="{{ $id }}" data-customer-id="{{ $id }}">
            @endforeach
        </div>
    </div>

    <div class="em-audience-builder__section" data-audience-panel="manual" @if($currentSource !== 'manual') hidden @endif>
        <div class="em-audience-builder__label">
            <strong>2. Paste addresses</strong>
            <span>One email per line, or comma-separated.</span>
        </div>
        <textarea id="manual_emails" name="manual_emails" class="form-control radius-8" rows="6" placeholder="parent1@email.com&#10;parent2@email.com" data-audience-input>{{ old('manual_emails', $filters['manual_emails'] ?? '') }}</textarea>
    </div>

    <div class="em-audience-builder__section">
        <div class="em-audience-builder__label">
            <strong>3. Delivery estimate</strong>
            <span>Live count after suppressions & invalid emails.</span>
        </div>
        <div class="em-preflight-card" data-audience-preflight>
            <div class="em-preflight-card__grid">
                <div class="em-preflight-stat em-preflight-stat--primary"><strong data-pf-eligible>—</strong><span>Will receive</span></div>
                <div class="em-preflight-stat"><strong data-pf-selected>—</strong><span>Selected</span></div>
                <div class="em-preflight-stat"><strong data-pf-unsub>—</strong><span>Excluded</span></div>
                <div class="em-preflight-stat"><strong data-pf-invalid>—</strong><span>Invalid</span></div>
            </div>
            <p class="mb-8 text-secondary-light text-sm" data-pf-message>Choose an audience to see delivery estimate.</p>
            <div class="em-preflight-sample" data-pf-sample hidden>
                <span class="em-preflight-sample__label">Sample recipients</span>
                <ul data-pf-sample-list></ul>
            </div>
        </div>
    </div>
</div>
