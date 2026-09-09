<article class="int-list-row int-list-row--mapping int-list-row--facebook">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <div class="crm-list-row__identity-copy min-w-0">
            <span class="crm-list-row__name">{{ $mapping->external_form_name }}</span>
            <span class="crm-list-row__contact">Facebook Lead Form</span>
        </div>
    </div>
    <div class="crm-list-row__field">
        @can('manage integrations')
        <form method="POST" action="{{ route('admin.integrations.facebook.mappings.update', $mapping) }}" class="int-mapping-form">
            @csrf @method('PUT')
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="internal_label" class="form-control form-control-sm radius-8" value="{{ old('internal_label', $mapping->internal_label) }}" placeholder="e.g. Student enquiry" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="lead_source_label" class="form-control form-control-sm radius-8" value="{{ old('lead_source_label', $mapping->lead_source_label) }}" placeholder="Lead source label">
                </div>
                <div class="col-md-2">
                    <select name="assigned_to" class="form-select form-select-sm radius-8">
                        <option value="">Unassigned</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" @selected($mapping->assigned_to == $admin->id)>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select form-select-sm radius-8" required>
                        @foreach(['low','medium','high'] as $priority)
                            <option value="{{ $priority }}" @selected($mapping->priority === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-8 align-items-center">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="auto_create_lead" value="1" id="auto_{{ $mapping->id }}" @checked($mapping->auto_create_lead)>
                        <label class="form-check-label text-sm" for="auto_{{ $mapping->id }}">Auto</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary-600 radius-8">Save</button>
                </div>
            </div>
        </form>
        @else
        {{ $mapping->internal_label }}
        @endcan
    </div>
    <div class="crm-list-row__actions">
        @can('manage integrations')
        <form method="POST" action="{{ route('admin.integrations.facebook.mappings.destroy', $mapping) }}" onsubmit="return confirm('Remove this form mapping?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger-600 radius-8">Remove</button>
        </form>
        @endcan
    </div>
</article>
