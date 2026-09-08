@php
    $categories = $categories ?? collect();
    $displayName = trim(old('first_name', $lead->first_name).' '.old('last_name', $lead->last_name)) ?: $lead->full_name;
@endphp
<div class="crm-lead-panel crm-lead-ticket crm-lead-ticket--form" data-crm-lead-panel-edit data-lead-id="{{ $lead->id }}">
    <form method="POST"
          action="{{ route('admin.crm.leads.update', $lead) }}"
          class="crm-lead-ticket__form"
          data-crm-panel-edit-form
          data-lead-id="{{ $lead->id }}">
        @csrf
        @method('PUT')

        <header class="crm-lead-ticket__header">
            <div class="crm-lead-ticket__identity">
                <span class="crm-lead-avatar crm-lead-avatar--panel" aria-hidden="true">{{ \App\Support\UserManagementHelper::initials($displayName) }}</span>
                <div class="min-w-0">
                    <div class="crm-lead-ticket__ref">Lead #{{ $lead->id }} · Editing</div>
                    <h2 class="crm-lead-ticket__title" data-crm-panel-title>{{ $displayName }}</h2>
                    <p class="crm-lead-ticket__form-hint">Update details — same layout as view mode.</p>
                </div>
            </div>

            <div class="crm-lead-ticket__controls crm-lead-ticket__controls--form">
                @include('admin.crm.leads.partials.form-tag-select', [
                    'name' => 'lead_status',
                    'value' => old('lead_status', $lead->lead_status),
                    'options' => $statusFormOptions,
                    'required' => true,
                    'ariaLabel' => 'Status',
                ])
                @include('admin.crm.leads.partials.form-tag-select', [
                    'name' => 'priority',
                    'value' => old('priority', $lead->priority),
                    'options' => $priorityFormOptions,
                    'required' => true,
                    'ariaLabel' => 'Priority',
                ])
                @if($categories->isNotEmpty())
                    @include('admin.crm.leads.partials.form-tag-select', [
                        'name' => 'lead_category_id',
                        'value' => old('lead_category_id', $lead->lead_category_id ?? ''),
                        'options' => $categoryFormOptions,
                        'ariaLabel' => 'Category',
                    ])
                @endif
            </div>
        </header>

        <div class="crm-lead-ticket__layout">
            <div class="crm-lead-ticket__main">
                <section class="crm-lead-ticket__block crm-lead-form-block">
                    <h3 class="crm-lead-ticket__block-title"><iconify-icon icon="solar:user-linear"></iconify-icon> Contact</h3>
                    <div class="crm-lead-form-grid">
                        @include('admin.crm.leads.partials.form-field', ['label' => 'First name', 'name' => 'first_name', 'value' => old('first_name', $lead->first_name), 'required' => true])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Last name', 'name' => 'last_name', 'value' => old('last_name', $lead->last_name)])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'value' => old('email', $lead->email)])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Phone', 'name' => 'phone', 'value' => old('phone', $lead->phone)])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Company', 'name' => 'company', 'value' => old('company', $lead->company)])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Source', 'name' => 'lead_source', 'value' => old('lead_source', $lead->lead_source)])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Description', 'name' => 'lead_description', 'type' => 'textarea', 'value' => old('lead_description', $lead->lead_description), 'wide' => true])
                    </div>
                </section>

                <details class="crm-lead-ticket__block crm-lead-ticket__block--collapsible crm-lead-form-block">
                    <summary class="crm-lead-ticket__block-head crm-lead-ticket__block-head--toggle">
                        <h3 class="crm-lead-ticket__block-title"><iconify-icon icon="solar:settings-linear"></iconify-icon> More fields</h3>
                        <span class="crm-lead-ticket__block-meta">Address, follow-up, value</span>
                    </summary>
                    <div class="crm-lead-form-grid">
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Title', 'name' => 'title', 'value' => old('title', $lead->title)])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Estimated value', 'name' => 'estimated_value', 'type' => 'number', 'value' => old('estimated_value', $lead->estimated_value)])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Probability (%)', 'name' => 'probability', 'type' => 'number', 'value' => old('probability', $lead->probability)])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Follow-up date', 'name' => 'next_follow_up_date', 'type' => 'date', 'value' => old('next_follow_up_date', optional($lead->next_follow_up_date)->format('Y-m-d'))])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Address', 'name' => 'address', 'value' => old('address', $lead->address), 'wide' => true])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'City', 'name' => 'city', 'value' => old('city', $lead->city)])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Postal code', 'name' => 'postal_code', 'value' => old('postal_code', $lead->postal_code)])
                    </div>
                </details>

                <div class="crm-lead-panel__edit-errors d-none" data-crm-panel-edit-errors></div>
            </div>

            <aside class="crm-lead-ticket__sidebar">
                <h3 class="crm-lead-ticket__sidebar-title">Details</h3>
                <dl class="crm-lead-ticket__properties">
                    <div class="crm-lead-ticket__property">
                        <dt>Assigned</dt>
                        <dd>
                            @include('admin.crm.leads.partials.form-tag-select', [
                                'name' => 'assigned_to',
                                'value' => old('assigned_to', $lead->assigned_to ?? ''),
                                'options' => $assigneeFormOptions,
                                'owner' => true,
                                'ariaLabel' => 'Assigned to',
                            ])
                        </dd>
                    </div>
                    <div class="crm-lead-ticket__property">
                        <dt>Created</dt>
                        <dd>{{ optional($lead->created_at)->format('M j, Y') ?? '—' }}</dd>
                    </div>
                    @if($lead->leadImport)
                        <div class="crm-lead-ticket__property">
                            <dt>Import file</dt>
                            <dd class="crm-lead-ticket__import-chips">
                                <span class="crm-field-chip crm-field-chip--neutral crm-field-chip--value-only">{{ Str::limit($lead->leadImport->original_filename, 36) }}</span>
                            </dd>
                        </div>
                    @endif
                </dl>
            </aside>
        </div>

        <div class="crm-lead-ticket__toolbar crm-lead-ticket__toolbar--form">
            <button type="button" class="crm-lead-ticket__tool" data-crm-panel-cancel>
                <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                <span>Cancel</span>
            </button>
            <button type="submit" class="crm-lead-ticket__tool crm-lead-ticket__tool--primary" data-crm-panel-save>
                <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                <span>Save changes</span>
            </button>
        </div>
    </form>
</div>
