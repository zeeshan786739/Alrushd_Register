@php
    $categories = $categories ?? collect();
@endphp
<div class="crm-lead-panel crm-lead-ticket crm-lead-ticket--form crm-lead-ticket--create" data-crm-lead-create-panel>
    <form method="POST"
          action="{{ route('admin.crm.leads.store') }}"
          class="crm-lead-ticket__form"
          data-crm-lead-create-form>
        @csrf

        <header class="crm-lead-ticket__header">
            <div class="crm-lead-ticket__identity">
                <span class="crm-lead-avatar crm-lead-avatar--panel" aria-hidden="true">+</span>
                <div class="min-w-0">
                    <div class="crm-lead-ticket__ref">New lead</div>
                    <h2 class="crm-lead-ticket__title" data-crm-panel-title>Create lead</h2>
                    <p class="crm-lead-ticket__form-hint">Same layout as viewing a lead — add without leaving the board.</p>
                </div>
            </div>

            <div class="crm-lead-ticket__controls crm-lead-ticket__controls--form">
                @include('admin.crm.leads.partials.form-tag-select', [
                    'name' => 'lead_status',
                    'value' => old('lead_status', 'new'),
                    'options' => $statusFormOptions,
                    'required' => true,
                    'ariaLabel' => 'Status',
                ])
                @include('admin.crm.leads.partials.form-tag-select', [
                    'name' => 'priority',
                    'value' => old('priority', 'medium'),
                    'options' => $priorityFormOptions,
                    'required' => true,
                    'ariaLabel' => 'Priority',
                ])
                @if($categories->isNotEmpty())
                    @include('admin.crm.leads.partials.form-tag-select', [
                        'name' => 'lead_category_id',
                        'value' => old('lead_category_id', ''),
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
                        @include('admin.crm.leads.partials.form-field', ['label' => 'First name', 'name' => 'first_name', 'value' => old('first_name'), 'required' => true])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Last name', 'name' => 'last_name', 'value' => old('last_name')])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'value' => old('email')])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Phone', 'name' => 'phone', 'value' => old('phone')])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Company', 'name' => 'company', 'value' => old('company')])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Source', 'name' => 'lead_source', 'value' => old('lead_source')])
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Description', 'name' => 'lead_description', 'type' => 'textarea', 'value' => old('lead_description'), 'wide' => true])
                    </div>
                </section>

                <details class="crm-lead-ticket__block crm-lead-ticket__block--collapsible crm-lead-form-block">
                    <summary class="crm-lead-ticket__block-head crm-lead-ticket__block-head--toggle">
                        <h3 class="crm-lead-ticket__block-title"><iconify-icon icon="solar:settings-linear"></iconify-icon> More fields</h3>
                        <span class="crm-lead-ticket__block-meta">Optional value</span>
                    </summary>
                    <div class="crm-lead-form-grid">
                        @include('admin.crm.leads.partials.form-field', ['label' => 'Estimated value', 'name' => 'estimated_value', 'type' => 'number', 'value' => old('estimated_value')])
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
                                'value' => old('assigned_to', ''),
                                'options' => $assigneeFormOptions,
                                'owner' => true,
                                'ariaLabel' => 'Assigned to',
                            ])
                        </dd>
                    </div>
                </dl>
            </aside>
        </div>

        <div class="crm-lead-ticket__toolbar crm-lead-ticket__toolbar--form">
            <button type="button" class="crm-lead-ticket__tool" data-bs-dismiss="modal">
                <iconify-icon icon="solar:close-circle-linear"></iconify-icon>
                <span>Cancel</span>
            </button>
            <button type="submit" class="crm-lead-ticket__tool crm-lead-ticket__tool--primary" data-crm-panel-save>
                <iconify-icon icon="solar:add-circle-linear"></iconify-icon>
                <span>Create lead</span>
            </button>
        </div>
    </form>
</div>
