@php
    $categories = $categories ?? collect();
@endphp
<div class="crm-lead-panel crm-lead-panel--edit" data-crm-lead-panel-edit data-lead-id="{{ $lead->id }}">
    <header class="crm-lead-panel__header crm-lead-panel__header--edit">
        <div>
            <div class="crm-lead-panel__ref">Lead #{{ $lead->id }}</div>
            <h2 class="crm-lead-panel__title">Edit lead</h2>
            <p class="crm-lead-panel__edit-subtitle">Update details without leaving the workspace.</p>
        </div>
    </header>

    <form method="POST"
          action="{{ route('admin.crm.leads.update', $lead) }}"
          class="crm-lead-panel__edit-form"
          data-crm-panel-edit-form
          data-lead-id="{{ $lead->id }}">
        @csrf
        @method('PUT')

        <div class="crm-lead-panel__edit-body">
            <div class="crm-lead-panel__edit-grid">
                <label class="crm-lead-panel__field">
                    <span>Title</span>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $lead->title) }}" autocomplete="off">
                </label>
                <label class="crm-lead-panel__field">
                    <span>First name *</span>
                    <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $lead->first_name) }}" required autocomplete="off">
                </label>
                <label class="crm-lead-panel__field">
                    <span>Last name</span>
                    <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $lead->last_name) }}" autocomplete="off">
                </label>
                <label class="crm-lead-panel__field">
                    <span>Email</span>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $lead->email) }}" autocomplete="off">
                </label>
                <label class="crm-lead-panel__field">
                    <span>Phone</span>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $lead->phone) }}" autocomplete="off">
                </label>
                <label class="crm-lead-panel__field">
                    <span>Company</span>
                    <input type="text" name="company" class="form-control" value="{{ old('company', $lead->company) }}" autocomplete="off">
                </label>
                <label class="crm-lead-panel__field">
                    <span>Source</span>
                    <input type="text" name="lead_source" class="form-control" value="{{ old('lead_source', $lead->lead_source) }}" autocomplete="off">
                </label>
                @if($categories->isNotEmpty())
                    <label class="crm-lead-panel__field">
                        <span>Category</span>
                        <select name="lead_category_id" class="form-select">
                            <option value="">Uncategorized</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('lead_category_id', $lead->lead_category_id) === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                <label class="crm-lead-panel__field">
                    <span>Status</span>
                    <select name="lead_status" class="form-select" required>
                        @foreach(\App\Enums\LeadStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('lead_status', $lead->lead_status) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="crm-lead-panel__field">
                    <span>Priority</span>
                    <select name="priority" class="form-select" required>
                        @foreach(\App\Enums\LeadPriority::cases() as $priority)
                            <option value="{{ $priority->value }}" @selected(old('priority', $lead->priority) === $priority->value)>{{ $priority->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="crm-lead-panel__field">
                    <span>Assigned to</span>
                    <select name="assigned_to" class="form-select">
                        <option value="">Unassigned</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" @selected((string) old('assigned_to', $lead->assigned_to) === (string) $admin->id)>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="crm-lead-panel__field">
                    <span>Estimated value</span>
                    <input type="number" step="0.01" min="0" name="estimated_value" class="form-control" value="{{ old('estimated_value', $lead->estimated_value) }}">
                </label>
                <label class="crm-lead-panel__field">
                    <span>Probability (%)</span>
                    <input type="number" min="0" max="100" name="probability" class="form-control" value="{{ old('probability', $lead->probability) }}">
                </label>
                <label class="crm-lead-panel__field">
                    <span>Follow-up date</span>
                    <input type="date" name="next_follow_up_date" class="form-control" value="{{ old('next_follow_up_date', optional($lead->next_follow_up_date)->format('Y-m-d')) }}">
                </label>
                <label class="crm-lead-panel__field crm-lead-panel__field--wide">
                    <span>Address</span>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $lead->address) }}" autocomplete="off">
                </label>
                <label class="crm-lead-panel__field">
                    <span>City</span>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $lead->city) }}" autocomplete="off">
                </label>
                <label class="crm-lead-panel__field">
                    <span>Postal code</span>
                    <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $lead->postal_code) }}" autocomplete="off">
                </label>
                <label class="crm-lead-panel__field crm-lead-panel__field--wide">
                    <span>Description</span>
                    <textarea name="lead_description" class="form-control" rows="4">{{ old('lead_description', $lead->lead_description) }}</textarea>
                </label>
            </div>

            <div class="crm-lead-panel__edit-errors" data-crm-panel-edit-errors hidden></div>
        </div>

        <div class="crm-lead-panel__edit-actions">
            <button type="button" class="crm-lead-panel__tool" data-crm-panel-cancel>
                <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                <span>Cancel</span>
            </button>
            <button type="submit" class="crm-lead-panel__tool crm-lead-panel__tool--primary" data-crm-panel-save>
                <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                <span>Save changes</span>
            </button>
        </div>
    </form>
</div>
