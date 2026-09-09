@canany(['update leads', 'assign leads'])
@if(($hasActiveFilters ?? false) && ($filteredTotal ?? 0) > 0)
<div class="crm-filter-bulk-bar" data-crm-filter-bulk-bar>
    <div class="crm-filter-bulk-bar__summary">
        <iconify-icon icon="solar:filter-linear" aria-hidden="true"></iconify-icon>
        <strong>{{ number_format($filteredTotal) }}</strong>
        <span>lead(s) match your current filters</span>
    </div>
    <div class="crm-filter-bulk-bar__actions">
        @can('assign leads')
            <label class="crm-filter-bulk-bar__field">
                <span>Assign all matching</span>
                <select class="form-select form-select-sm" data-crm-filter-bulk-assignee>
                    <option value="">Choose teammate…</option>
                    <option value="__unassigned__">Unassigned</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="button"
                    class="crm-filter-bulk-bar__btn"
                    data-crm-filter-bulk-apply="assigned_to"
                    disabled>
                Apply
            </button>
        @endcan
        @can('update leads')
            @can('assign leads')
                <span class="crm-filter-bulk-bar__divider" aria-hidden="true"></span>
            @endcan
            <label class="crm-filter-bulk-bar__field">
                <span>Move all to</span>
                <select class="form-select form-select-sm" data-crm-filter-bulk-status>
                    <option value="">Choose status…</option>
                    @foreach(\App\Enums\LeadStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </label>
            <button type="button"
                    class="crm-filter-bulk-bar__btn"
                    data-crm-filter-bulk-apply="lead_status"
                    disabled>
                Apply
            </button>
        @endcan
    </div>
</div>
@endif
@endcanany
