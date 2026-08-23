@extends('admin.layouts.app')
@section('title', $customer->name)
@section('content')
@include('admin.crm.partials.styles')
@php
    $activityIcons = [
        'call' => 'solar:phone-linear',
        'email' => 'solar:letter-linear',
        'meeting' => 'solar:users-group-rounded-linear',
        'note' => 'solar:notes-linear',
        'task' => 'solar:checklist-linear',
    ];
    $projects = $customer->projects->sortByDesc('updated_at');
    $quotations = $customer->quotations->sortByDesc('created_at');
    $invoices = $customer->invoices->sortByDesc('created_at');
@endphp
<div class="dashboard-main-body">
    <div class="crm-workspace-header mb-20">
        <div class="crm-workspace-header__top">
            <div class="min-w-0">
                <div class="text-sm text-secondary-light mb-4">
                    <a href="{{ route('admin.crm.customers.index') }}" class="text-secondary-light text-decoration-none">Customers</a>
                    <span class="mx-4">/</span>
                    <span>{{ $customer->name }}</span>
                </div>
                <h1 class="crm-workspace-header__title">{{ $customer->name }}</h1>
                @if($customer->company)
                    <div class="text-sm text-secondary-light mb-8">{{ $customer->company }}</div>
                @endif
                <div class="crm-workspace-header__contact">
                    @if($customer->email)
                        <span><iconify-icon icon="solar:letter-linear"></iconify-icon> <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></span>
                    @endif
                    @if($customer->phone)
                        <span><iconify-icon icon="solar:phone-linear"></iconify-icon> <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a></span>
                    @endif
                    <span><iconify-icon icon="solar:user-linear"></iconify-icon> {{ $customer->assignedAdmin?->name ?? 'Unassigned' }}</span>
                    <span><iconify-icon icon="solar:calendar-linear"></iconify-icon> Customer since {{ $customer->created_at?->format('M j, Y') }}</span>
                    @if($customer->source)
                        <span><iconify-icon icon="solar:global-linear"></iconify-icon> {{ $customer->source }}</span>
                    @endif
                </div>
                <div class="crm-workspace-header__badges">
                    @include('admin.crm.partials.status-pill', ['status'=>$customer->status])
                    <span class="crm-status-pill crm-status-pill--accepted">LTV {{ number_format($commercial['lifetime_value'], 2) }}</span>
                    @if($customer->lead)
                        <span class="crm-status-pill crm-status-pill--qualified">From Lead</span>
                    @endif
                </div>
            </div>
            <div class="crm-workspace-header__actions">
                @can('update customers')
                    <a href="{{ route('admin.crm.customers.edit', $customer) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:pen-linear"></iconify-icon> Edit</a>
                    <a href="#customer-contacts" class="btn btn-outline-neutral-500 radius-8 px-16 py-10"><iconify-icon icon="solar:user-plus-linear"></iconify-icon> Add Contact</a>
                @endcan
                @if($customer->email)
                    <a href="mailto:{{ $customer->email }}" class="btn btn-outline-neutral-500 radius-8 px-16 py-10"><iconify-icon icon="solar:letter-linear"></iconify-icon> Email</a>
                @endif
                @can('compose emails')
                    @if($customer->email)
                        <a href="{{ route('admin.email.compose', ['to'=>$customer->email,'subject'=>'Follow up with '.$customer->name]) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:pen-new-square-linear"></iconify-icon> Compose</a>
                    @endif
                @endcan
                @can('create projects')
                    <a href="{{ route('admin.crm.projects.create', ['customer_id'=>$customer->id]) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:folder-with-files-linear"></iconify-icon> Project</a>
                @endcan
                @can('create quotations')
                    <a href="{{ route('admin.crm.quotations.create', ['customer_id'=>$customer->id]) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:document-text-linear"></iconify-icon> Quotation</a>
                @endcan
                @can('create invoices')
                    <a href="{{ route('admin.crm.invoices.create', ['customer_id'=>$customer->id]) }}" class="btn btn-primary-600 radius-8 px-16 py-10"><iconify-icon icon="solar:bill-list-linear"></iconify-icon> Invoice</a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row g-3 mb-20 crm-commercial-grid">
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Projects</div><p class="crm-commercial-stat__value">{{ $commercial['projects_total'] }}</p><p class="crm-commercial-stat__hint">{{ $commercial['projects_active'] }} active</p></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Quotations</div><p class="crm-commercial-stat__value">{{ $commercial['quotations_total'] }}</p><p class="crm-commercial-stat__hint">Accepted {{ number_format($commercial['quotations_accepted_value'], 2) }}</p></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Invoiced</div><p class="crm-commercial-stat__value">{{ number_format($commercial['invoiced_amount'], 2) }}</p><p class="crm-commercial-stat__hint">{{ $commercial['invoices_total'] }} invoices</p></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Paid</div><p class="crm-commercial-stat__value">{{ number_format($commercial['paid_amount'], 2) }}</p><p class="crm-commercial-stat__hint">Collected</p></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Outstanding</div><p class="crm-commercial-stat__value">{{ number_format($commercial['outstanding_amount'], 2) }}</p><p class="crm-commercial-stat__hint">Due balance</p></div></div>
        <div class="col-6 col-md-4 col-xl-2"><div class="crm-commercial-stat"><div class="crm-commercial-stat__label">Lifetime value</div><p class="crm-commercial-stat__value">{{ number_format($commercial['lifetime_value'], 2) }}</p><p class="crm-commercial-stat__hint">Stored LTV</p></div></div>
    </div>

    @if($customer->lead)
    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            <h6 class="crm-section-title"><iconify-icon icon="solar:user-speak-linear"></iconify-icon> Original Lead</h6>
            <div class="row g-3 align-items-center">
                <div class="col-md-8">
                    <div class="fw-semibold">{{ $customer->lead->full_name }}</div>
                    <div class="crm-lead-meta mt-4">
                        Source: {{ $customer->lead->lead_source ?? $customer->lead->source ?? '—' }}
                        · Status: {{ $customer->lead->lead_status }}
                        @if($customer->lead->converted_at)
                            · Converted {{ $customer->lead->converted_at->format('M j, Y') }}
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    @can('view leads')
                        <a href="{{ route('admin.crm.leads.show', $customer->lead) }}" class="btn btn-outline-primary-600 radius-8 px-16 py-10">View Lead</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-3 mb-20">
        <div class="col-xl-7" id="customer-contacts">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <div class="d-flex justify-content-between align-items-center mb-8">
                        <h6 class="crm-section-title mb-0"><iconify-icon icon="solar:users-group-rounded-linear"></iconify-icon> Contacts</h6>
                    </div>
                    @forelse($customer->contacts as $contact)
                        <div class="crm-contact-row">
                            <div class="min-w-0">
                                <div class="fw-semibold">
                                    {{ $contact->name }}
                                    @if($contact->is_primary)<span class="badge bg-primary-50 text-primary-600 ms-4">Primary</span>@endif
                                </div>
                                <div class="crm-lead-meta">
                                    {{ $contact->position ?: 'No title' }}
                                    · {{ $contact->email ?: 'No email' }}
                                    · {{ $contact->phone ?: 'No phone' }}
                                </div>
                            </div>
                            @can('update customers')
                                <form method="POST" action="{{ route('admin.crm.customers.contacts.destroy', [$customer, $contact->id]) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger radius-8">Remove</button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <div class="crm-empty-state"><iconify-icon icon="solar:user-plus-linear"></iconify-icon>No contacts yet. Add a primary contact below.</div>
                    @endforelse

                    @can('update customers')
                    <form method="POST" action="{{ route('admin.crm.customers.contacts.store', $customer) }}" class="row g-2 mt-16 pt-12 border-top">
                        @csrf
                        <div class="col-md-3"><input name="name" class="form-control radius-8" placeholder="Name *" required></div>
                        <div class="col-md-2"><input name="position" class="form-control radius-8" placeholder="Role / title"></div>
                        <div class="col-md-3"><input name="email" type="email" class="form-control radius-8" placeholder="Email"></div>
                        <div class="col-md-2"><input name="phone" class="form-control radius-8" placeholder="Phone"></div>
                        <div class="col-md-2 d-flex align-items-center gap-8">
                            <label class="form-check mb-0 text-sm"><input type="checkbox" name="is_primary" value="1" class="form-check-input"> Primary</label>
                        </div>
                        <div class="col-12"><button class="btn btn-primary-600 radius-8 px-16 py-10">Add Contact</button></div>
                    </form>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="crm-section-title"><iconify-icon icon="solar:info-circle-linear"></iconify-icon> Profile details</h6>
                    <div class="row g-3">
                        <div class="col-6"><div class="crm-lead-meta">Website</div><div>{{ $customer->website ?: '—' }}</div></div>
                        <div class="col-6"><div class="crm-lead-meta">Last contacted</div><div>{{ $customer->last_contacted_at?->format('M j, Y') ?? '—' }}</div></div>
                        <div class="col-12"><div class="crm-lead-meta">Address</div><div>{{ collect([$customer->address,$customer->city,$customer->state,$customer->zip_code,$customer->country])->filter()->implode(', ') ?: '—' }}</div></div>
                        <div class="col-12"><div class="crm-lead-meta">Notes</div><div>{{ $customer->notes ?: '—' }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-8 mb-12">
                <h6 class="crm-section-title mb-0"><iconify-icon icon="solar:folder-with-files-linear"></iconify-icon> Projects</h6>
                @can('create projects')
                    <a href="{{ route('admin.crm.projects.create', ['customer_id'=>$customer->id]) }}" class="btn btn-sm btn-outline-primary-600 radius-8">Create Project</a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 crm-relation-table">
                    <thead><tr><th>Project</th><th>Status</th><th>Progress</th><th>Due</th></tr></thead>
                    <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('admin.crm.projects.show', $project) }}">{{ $project->name }}</a>
                                <div class="crm-lead-meta">{{ $project->project_code }}</div>
                            </td>
                            <td>@include('admin.crm.partials.status-pill', ['status'=>$project->status])</td>
                            <td>{{ $project->progress !== null ? $project->progress.'%' : '—' }}</td>
                            <td>{{ $project->end_date?->format('M j, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="crm-empty-state py-20 mb-0"><iconify-icon icon="solar:folder-with-files-linear"></iconify-icon>No projects linked to this customer.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-8 mb-12">
                <h6 class="crm-section-title mb-0"><iconify-icon icon="solar:document-text-linear"></iconify-icon> Quotations</h6>
                @can('create quotations')
                    <a href="{{ route('admin.crm.quotations.create', ['customer_id'=>$customer->id]) }}" class="btn btn-sm btn-outline-primary-600 radius-8">Create Quotation</a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 crm-relation-table">
                    <thead><tr><th>Quotation</th><th>Project</th><th>Status</th><th>Total</th><th>Dates</th></tr></thead>
                    <tbody>
                    @forelse($quotations as $quotation)
                        <tr>
                            <td><a href="{{ route('admin.crm.quotations.show', $quotation) }}">{{ $quotation->quotation_number }}</a></td>
                            <td>{{ $quotation->project?->name ?? '—' }}</td>
                            <td>@include('admin.crm.partials.status-pill', ['status'=>$quotation->status])</td>
                            <td class="fw-semibold">{{ number_format((float) $quotation->total, 2) }}</td>
                            <td>
                                <div>{{ $quotation->quotation_date?->format('M j, Y') ?? '—' }}</div>
                                <div class="crm-lead-meta">Valid {{ $quotation->valid_until?->format('M j, Y') ?? '—' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="crm-empty-state py-20 mb-0"><iconify-icon icon="solar:document-text-linear"></iconify-icon>No quotations yet.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card radius-12 shadow-2 border-0 mb-20">
        <div class="card-body p-24">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-8 mb-12">
                <h6 class="crm-section-title mb-0"><iconify-icon icon="solar:bill-list-linear"></iconify-icon> Invoices</h6>
                @can('create invoices')
                    <a href="{{ route('admin.crm.invoices.create', ['customer_id'=>$customer->id]) }}" class="btn btn-sm btn-outline-primary-600 radius-8">Create Invoice</a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 crm-relation-table">
                    <thead><tr><th>Invoice</th><th>Status</th><th>Total</th><th>Paid</th><th>Outstanding</th><th>Due</th></tr></thead>
                    <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('admin.crm.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                            <td>@include('admin.crm.partials.status-pill', ['status'=>$invoice->status])</td>
                            <td class="fw-semibold">{{ number_format((float) $invoice->total, 2) }}</td>
                            <td>{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format((float) $invoice->due_amount, 2) }}</td>
                            <td>{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="crm-empty-state py-20 mb-0"><iconify-icon icon="solar:bill-list-linear"></iconify-icon>No invoices yet.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.crm.partials.email-history-list')

    <div class="card radius-12 shadow-2 border-0 crm-activity-card mb-20">
        <div class="card-body p-24">
            <h6 class="crm-section-title"><iconify-icon icon="solar:history-linear"></iconify-icon> Activity</h6>
            <div class="crm-activity-scroll mb-16">
                @forelse($customer->activities as $activity)
                    <div class="crm-activity-item">
                        <div class="crm-activity-icon"><iconify-icon icon="{{ $activityIcons[$activity->type] ?? 'solar:info-circle-linear' }}"></iconify-icon></div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex justify-content-between gap-12 flex-wrap">
                                <div class="fw-semibold">{{ $activity->subject ?: ucfirst($activity->type) }}</div>
                                <div class="crm-lead-meta">{{ $activity->activity_date?->format('M j, Y g:i A') ?? $activity->created_at?->format('M j, Y g:i A') }}</div>
                            </div>
                            @if($activity->description)
                                <div class="text-sm mt-4">{{ $activity->description }}</div>
                            @endif
                            <div class="crm-lead-meta mt-4">{{ $activity->admin?->name ?? 'System' }} · {{ ucfirst($activity->status ?? 'completed') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="crm-empty-state"><iconify-icon icon="solar:history-linear"></iconify-icon>No activity logged yet.</div>
                @endforelse
            </div>
            @can('update customers')
            <form method="POST" action="{{ route('admin.crm.customers.activities.store', $customer) }}" class="border-top pt-16">
                @csrf
                <div class="row g-2">
                    <div class="col-md-3">
                        <select name="type" class="form-select radius-8">
                            <option value="note">Note</option>
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="meeting">Meeting</option>
                            <option value="task">Task</option>
                        </select>
                    </div>
                    <div class="col-md-9"><input name="subject" class="form-control radius-8" placeholder="Subject"></div>
                    <div class="col-12"><textarea name="description" class="form-control radius-8" rows="2" placeholder="Details"></textarea></div>
                    <div class="col-12"><button class="btn btn-primary-600 radius-8 px-16 py-10">Log Activity</button></div>
                </div>
            </form>
            @endcan
        </div>
    </div>
</div>
@endsection
