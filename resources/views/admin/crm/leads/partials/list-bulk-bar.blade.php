@canany(['update leads', 'assign leads'])
<div class="crm-list-bulk-bar" data-crm-bulk-bar hidden>
    <div class="crm-list-bulk-bar__summary">
        <strong data-crm-bulk-count>0</strong>
        <span>selected</span>
    </div>
    <div class="crm-list-bulk-bar__actions">
        @can('update leads')
            <label class="crm-list-bulk-bar__field">
                <span>Status</span>
                <select class="form-select form-select-sm" data-crm-bulk-status>
                    <option value="">Choose…</option>
                    @foreach(\App\Enums\LeadStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </label>
            <button type="button" class="crm-list-bulk-bar__btn" data-crm-bulk-apply="lead_status" disabled>Apply</button>

            <span class="crm-list-bulk-bar__divider" aria-hidden="true"></span>

            <label class="crm-list-bulk-bar__field">
                <span>Priority</span>
                <select class="form-select form-select-sm" data-crm-bulk-priority>
                    <option value="">Choose…</option>
                    @foreach(\App\Enums\LeadPriority::cases() as $priority)
                        <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </label>
            <button type="button" class="crm-list-bulk-bar__btn" data-crm-bulk-apply="priority" disabled>Apply</button>
        @endcan

        @can('assign leads')
            @can('update leads')
                <span class="crm-list-bulk-bar__divider" aria-hidden="true"></span>
            @endcan
            <label class="crm-list-bulk-bar__field">
                <span>Assignee</span>
                <select class="form-select form-select-sm" data-crm-bulk-assignee>
                    <option value="">Choose…</option>
                    <option value="__unassigned__">Unassigned</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="button" class="crm-list-bulk-bar__btn" data-crm-bulk-apply="assigned_to" disabled>Apply</button>
        @endcan
    </div>
    <button type="button" class="crm-list-bulk-bar__clear" data-crm-bulk-clear aria-label="Clear selection">
        <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
        Clear
    </button>
</div>
@endcanany
