@extends('admin.layouts.app')
@section('title', 'Facebook Lead Ads')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Facebook Lead Ads',
        'subtitle' => 'Connect your Facebook Page and map Lead Forms to CRM',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'Integrations', 'url' => route('admin.integrations.hub')],
            ['label' => 'Facebook'],
        ],
    ])

    @if(session('success'))<div class="alert alert-success radius-8">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger radius-8">{{ session('error') }}</div>@endif

    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card radius-12 shadow-2 border-0">
                <div class="card-body p-24">
                    <h6 class="mb-16">Connection</h6>

                    @if($connection->isConnected())
                        <div class="mb-12">
                            <span class="badge {{ $connection->status->badgeClass() }} radius-8">{{ $connection->status->label() }}</span>
                        </div>
                        <p class="text-sm mb-8"><strong>Page:</strong> {{ $connection->external_account_name }}</p>
                        <p class="text-sm mb-8"><strong>Page ID:</strong> <code>{{ $connection->external_account_id }}</code></p>
                        <p class="text-sm mb-8"><strong>Webhook:</strong> {{ $connection->webhook_subscribed_at ? 'Subscribed '.$connection->webhook_subscribed_at->diffForHumans() : 'Not confirmed' }}</p>
                        <p class="text-sm mb-16"><strong>Last lead received:</strong> {{ $connection->last_webhook_at?->diffForHumans() ?? 'Never' }}</p>

                        @can('manage integrations')
                        <div class="d-flex flex-wrap gap-8">
                            <form method="POST" action="{{ route('admin.integrations.facebook.sync-forms') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary-600 radius-8">Sync Lead Forms</button>
                            </form>
                            @if(($unmappedCount ?? 0) + ($failedCount ?? 0) > 0)
                            <form method="POST" action="{{ route('admin.integrations.facebook.reprocess-pending') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning-600 radius-8">Reprocess pending ({{ ($unmappedCount ?? 0) + ($failedCount ?? 0) }})</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.integrations.facebook.disconnect') }}" onsubmit="return confirm('Disconnect Facebook for this school?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger-600 radius-8">Disconnect</button>
                            </form>
                        </div>
                        @endcan
                    @elseif($pendingPageSelection && count($pages) > 0)
                        <p class="text-sm text-secondary-light mb-16">Choose the Facebook Page that runs your Lead Ads for this school.</p>
                        <form method="POST" action="{{ route('admin.integrations.facebook.select-page') }}">
                            @csrf
                            <div class="mb-16">
                                <label class="form-label" for="page_id">Facebook Page</label>
                                <select name="page_id" id="page_id" class="form-select radius-8" required>
                                    <option value="">Select a page…</option>
                                    @foreach($pages as $page)
                                        <option value="{{ $page['id'] }}">{{ $page['name'] ?? $page['id'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary-600 radius-8 w-100">Connect this Page</button>
                        </form>
                    @else
                        <p class="text-sm text-secondary-light mb-16">
                            Connect with Facebook to authorize this school’s Page. Each school connects its own Page independently.
                        </p>
                        @can('manage integrations')
                        <a href="{{ route('admin.integrations.facebook.connect') }}" class="btn btn-primary-600 radius-8 w-100">
                            <iconify-icon icon="logos:facebook" class="me-8"></iconify-icon>
                            Connect with Facebook
                        </a>
                        @endcan
                    @endif
                </div>
            </div>

            <div class="card radius-12 shadow-2 border-0 mt-24">
                <div class="card-body p-24">
                    <h6 class="mb-12">Webhook URL</h6>
                    <p class="text-sm text-secondary-light mb-12">Add this in your Meta App → Webhooks → Page → <code>leadgen</code>.</p>
                    <div class="bg-neutral-50 radius-8 p-12">
                        <code class="text-sm d-block text-break">{{ $webhookUrl }}</code>
                    </div>
                    <p class="text-sm text-secondary-light mt-12 mb-0">
                        Verify token: configure <code>META_WEBHOOK_VERIFY_TOKEN</code> in your environment.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card radius-12 shadow-2 border-0 mb-24">
                <div class="card-body p-24">
                    <div class="d-flex align-items-center justify-content-between mb-16">
                        <h6 class="mb-0">Lead Form mappings</h6>
                        @if($connection->isConnected())
                            <span class="text-sm text-secondary-light">Map each Facebook form to student, teacher, etc.</span>
                        @endif
                    </div>

                    @if($connection->formMappings->isEmpty())
                        <div class="text-center py-32 text-secondary-light">
                            <iconify-icon icon="solar:document-linear" width="40" class="mb-12"></iconify-icon>
                            <p class="mb-0">No Lead Forms synced yet. Connect Facebook and click <strong>Sync Lead Forms</strong>, or add a form by ID below.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Facebook form</th>
                                        <th>Internal label</th>
                                        <th>Assignee</th>
                                        <th>Priority</th>
                                        <th>Auto lead</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($connection->formMappings as $mapping)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $mapping->external_form_name }}</div>
                                            <code class="text-xs">{{ $mapping->external_form_id }}</code>
                                        </td>
                                        <td colspan="4">
                                            @can('manage integrations')
                                            <form method="POST" action="{{ route('admin.integrations.facebook.mappings.update', $mapping) }}" class="row g-2 align-items-end">
                                                @csrf @method('PUT')
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
                                            </form>
                                            @else
                                            {{ $mapping->internal_label }}
                                            @endcan
                                        </td>
                                        <td class="text-end">
                                            @can('manage integrations')
                                            <form method="POST" action="{{ route('admin.integrations.facebook.mappings.destroy', $mapping) }}" onsubmit="return confirm('Remove this form mapping?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger-600 radius-8">Remove</button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @can('manage integrations')
                    @if($connection->isConnected())
                    <div class="border-top pt-20 mt-20">
                        <p class="text-sm fw-medium mb-12">Add another form</p>
                        <p class="text-sm text-secondary-light mb-12">Copy Form ID from Meta → Lead ads forms → click a form.</p>
                        <form method="POST" action="{{ route('admin.integrations.facebook.register-form') }}" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-md-5">
                                <label class="form-label text-sm" for="external_form_id">Form ID</label>
                                <input type="text" name="external_form_id" id="external_form_id" class="form-control form-control-sm radius-8" placeholder="e.g. 1953833178916110" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label text-sm" for="external_form_name">Form name (optional)</label>
                                <input type="text" name="external_form_name" id="external_form_name" class="form-control form-control-sm radius-8" placeholder="FitRaho CRM Test">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8 w-100">Add form</button>
                            </div>
                        </form>
                    </div>
                    @endif
                    @endcan
                </div>
            </div>

            <div class="card radius-12 shadow-2 border-0">
                <div class="card-body p-24">
                    <h6 class="mb-16">Recent imported leads</h6>
                    @if($recentSubmissions->isEmpty())
                        <p class="text-secondary-light mb-0">No Facebook leads received yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Mapping</th>
                                        <th>CRM Lead</th>
                                        <th>Received</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSubmissions as $submission)
                                    <tr>
                                        <td><span class="badge bg-neutral-200 text-secondary-light radius-8">{{ ucfirst($submission->status->value) }}</span></td>
                                        <td>{{ $submission->formMapping?->internal_label ?? 'Unmapped' }}</td>
                                        <td>
                                            @if($submission->lead_id)
                                                <a href="{{ route('admin.crm.leads.show', $submission->lead_id) }}">View lead</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $submission->created_at->diffForHumans() }}</td>
                                        <td>
                                            @can('manage integrations')
                                            @if(in_array($submission->status->value, ['unmapped', 'failed', 'pending']))
                                            <form method="POST" action="{{ route('admin.integrations.facebook.submissions.reprocess', $submission) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8">Reprocess</button>
                                            </form>
                                            @endif
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('admin.integrations.partials.go-live-checklist')
</div>
@endsection
