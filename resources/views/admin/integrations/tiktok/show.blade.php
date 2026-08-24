@extends('admin.layouts.app')
@section('title', 'TikTok Lead Ads')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'TikTok Lead Ads',
        'subtitle' => 'Connect a TikTok Ads account and send Instant Form leads into CRM',
        'showBreadcrumb' => true,
        'hideFlash' => true,
        'breadcrumbs' => [
            ['label' => 'Integrations', 'url' => route('admin.integrations.hub')],
            ['label' => 'TikTok'],
        ],
    ])

    <div class="card radius-12 shadow-2 border-0 mb-24">
        <div class="card-body p-24 p-md-32">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-20">
                <div class="d-flex align-items-start gap-16">
                    <span class="w-48-px h-48-px rounded-circle d-flex align-items-center justify-content-center bg-neutral-100 flex-shrink-0">
                        <iconify-icon icon="logos:tiktok-icon" width="28"></iconify-icon>
                    </span>
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-8 mb-8">
                            <h5 class="mb-0">TikTok Lead Ads</h5>
                            @if($connected)
                                <span class="badge {{ $connection->status->badgeClass() }} radius-8">{{ $connection->status->label() }}</span>
                            @elseif($pendingAdvertiserSelection)
                                <span class="badge bg-warning-focus text-warning-main radius-8">Select account</span>
                            @else
                                <span class="badge bg-neutral-200 text-secondary-light radius-8">Not Connected</span>
                            @endif
                        </div>
                        @if($connected)
                            <p class="text-sm mb-4"><strong>Advertiser account:</strong> {{ $connection->external_account_name ?? 'Connected' }}</p>
                            @if($connectedAt)
                                <p class="text-sm text-secondary-light mb-0"><strong>Connected:</strong> {{ $connectedAt }}</p>
                            @endif
                        @else
                            <p class="text-sm text-secondary-light mb-0">
                                Connect a TikTok Ads account and automatically send leads from TikTok Lead Generation Instant Forms into your CRM.
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex-shrink-0">
                    @can('manage integrations')
                        @if($pendingAdvertiserSelection)
                            <a href="{{ route('admin.integrations.tiktok.connect') }}" class="btn btn-outline-primary-600 radius-8">
                                Authorize a different account
                            </a>
                        @elseif($connected)
                            <a href="{{ route('admin.integrations.tiktok.connect') }}" class="btn btn-outline-primary-600 radius-8">
                                Reconnect TikTok
                            </a>
                        @else
                            <a href="{{ route('admin.integrations.tiktok.connect') }}" class="btn btn-primary-600 radius-8">
                                <iconify-icon icon="logos:tiktok-icon" class="me-8"></iconify-icon>
                                Connect TikTok
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if($pendingAdvertiserSelection)
        <div class="card radius-12 shadow-2 border-0 mb-24">
            <div class="card-body p-24 p-md-32">
                <h5 class="mb-8">Select TikTok Ads Account</h5>
                <p class="text-sm text-secondary-light mb-20">
                    Choose the TikTok Ads Manager account whose Lead Generation forms should connect to this organization.
                </p>
                <div class="row g-3">
                    @foreach($pendingAdvertisers as $advertiser)
                        <div class="col-12 col-lg-6">
                            <div class="border radius-8 p-20 h-100 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-16">
                                <div>
                                    <p class="fw-medium mb-4">{{ $advertiser['name'] }}</p>
                                    <p class="text-sm text-secondary-light mb-0">TikTok Ads account</p>
                                </div>
                                @can('manage integrations')
                                    <form method="POST" action="{{ route('admin.integrations.tiktok.select-advertiser') }}" class="flex-shrink-0">
                                        @csrf
                                        <input type="hidden" name="advertiser_id" value="{{ $advertiser['id'] }}">
                                        <button type="submit" class="btn btn-primary-600 radius-8">Connect This Account</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card radius-12 shadow-2 border-0 mb-24">
        <div class="card-body p-24">
            <h6 class="mb-4">How setup works</h6>
            <p class="text-sm text-secondary-light mb-20">Each school connects its own TikTok Ads account. Campaigns stay in TikTok Ads Manager — Enrolliq imports the leads.</p>
            <div class="row g-3">
                @foreach($setupSteps as $step)
                    <div class="col-12 col-sm-6 col-xl">
                        <div class="bg-neutral-50 radius-8 p-16 h-100">
                            <div class="d-flex flex-wrap align-items-center gap-8 mb-12">
                                <span class="badge bg-primary-50 text-primary-600 radius-8">{{ $step['n'] }}</span>
                                @if($step['state'] === 'complete')
                                    <span class="badge bg-success-focus text-success-main radius-8">Complete</span>
                                @elseif($step['state'] === 'current')
                                    <span class="badge bg-warning-focus text-warning-main radius-8">In progress</span>
                                @else
                                    <span class="badge bg-neutral-200 text-secondary-light radius-8">Pending</span>
                                @endif
                            </div>
                            <p class="fw-medium mb-4">{{ $step['title'] }}</p>
                            <p class="text-sm text-secondary-light mb-0">{{ $step['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-3 mb-24">
        <div class="col-sm-6 col-xl-3">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <span class="w-40-px h-40-px rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-50 mb-12">
                        <iconify-icon icon="solar:refresh-circle-linear" width="20" class="text-primary-600"></iconify-icon>
                    </span>
                    <h6 class="mb-8">Automatic Lead Sync</h6>
                    <p class="text-sm text-secondary-light mb-0">Leads from TikTok Instant Forms flow into this school’s CRM without manual export.</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <span class="w-40-px h-40-px rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-50 mb-12">
                        <iconify-icon icon="solar:document-text-linear" width="20" class="text-primary-600"></iconify-icon>
                    </span>
                    <h6 class="mb-8">Instant Form Mapping</h6>
                    <p class="text-sm text-secondary-light mb-0">Map each TikTok form to the right CRM source, assignee, and fields.</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <span class="w-40-px h-40-px rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-50 mb-12">
                        <iconify-icon icon="solar:lock-keyhole-linear" width="20" class="text-primary-600"></iconify-icon>
                    </span>
                    <h6 class="mb-8">Secure Account Connection</h6>
                    <p class="text-sm text-secondary-light mb-0">This school connects its own TikTok Ads account. Other organizations cannot see it.</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <span class="w-40-px h-40-px rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-50 mb-12">
                        <iconify-icon icon="solar:user-plus-linear" width="20" class="text-primary-600"></iconify-icon>
                    </span>
                    <h6 class="mb-8">CRM Lead Creation</h6>
                    <p class="text-sm text-secondary-light mb-0">Qualified submissions become CRM leads you can follow up on in Enrolliq.</p>
                </div>
            </div>
        </div>
    </div>

    @if($connected)
        <div class="card radius-12 shadow-2 border-0 mb-24">
            <div class="card-body p-24">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-16 mb-16">
                    <div>
                        <h6 class="mb-4">TikTok Lead Forms</h6>
                        <p class="text-sm text-secondary-light mb-0">
                            Instant Forms from this school’s connected TikTok Ads account.
                            @if($formsLastSyncedAt)
                                Last synced {{ $formsLastSyncedAt }}.
                            @endif
                        </p>
                    </div>
                    @can('manage integrations')
                        <form method="POST" action="{{ route('admin.integrations.tiktok.sync-forms') }}" class="flex-shrink-0">
                            @csrf
                            <button type="submit" class="btn btn-primary-600 radius-8">
                                Sync Lead Forms
                            </button>
                        </form>
                    @endcan
                </div>

                @if($formMappings->isEmpty())
                    <div class="text-center py-32 text-secondary-light">
                        <iconify-icon icon="solar:document-linear" width="40" class="mb-12"></iconify-icon>
                        <p class="fw-medium text-neutral-700 mb-8">No TikTok Lead Forms found</p>
                        <p class="text-sm mb-0">Create a Lead Generation Instant Form in TikTok Ads Manager, then sync again.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Form</th>
                                    <th>Status</th>
                                    <th>Mapping</th>
                                    <th>Assigned to</th>
                                    <th>Lead source</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formMappings as $form)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $form->external_form_name }}</div>
                                            <code class="text-xs">{{ $form->external_form_id }}</code>
                                        </td>
                                        <td>
                                            @if($form->external_status === 'PUBLISHED')
                                                <span class="badge bg-success-focus text-success-main radius-8">Ready</span>
                                            @elseif($form->external_status === 'EDITED')
                                                <span class="badge bg-warning-focus text-warning-main radius-8">Draft</span>
                                            @elseif(filled($form->external_status))
                                                <span class="badge bg-neutral-200 text-secondary-light radius-8">{{ $form->external_status }}</span>
                                            @else
                                                <span class="badge bg-neutral-200 text-secondary-light radius-8">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($form->mappingStatus() === 'configured')
                                                <span class="badge bg-success-focus text-success-main radius-8">Configured</span>
                                            @elseif($form->mappingStatus() === 'disabled')
                                                <span class="badge bg-neutral-200 text-secondary-light radius-8">Disabled</span>
                                            @else
                                                <span class="badge bg-warning-focus text-warning-main radius-8">Needs setup</span>
                                            @endif
                                        </td>
                                        <td class="text-sm">{{ $form->assignedAdmin?->name ?? 'Unassigned' }}</td>
                                        <td class="text-sm">{{ $form->lead_source_label ?: '—' }}</td>
                                        <td class="text-end">
                                            @can('manage integrations')
                                                <a href="{{ route('admin.integrations.tiktok.forms.configure', $form) }}" class="btn btn-sm btn-outline-primary-600 radius-8">Configure</a>
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
    @endif

    <div class="row g-3">
        <div class="col-xl-4">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <h6 class="mb-16">Setup status</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start justify-content-between gap-12 mb-16">
                            <span class="text-sm">Integration service</span>
                            <span class="badge {{ $configOk ? 'bg-success-focus text-success-main' : 'bg-neutral-200 text-secondary-light' }} radius-8 flex-shrink-0">{{ $setupStatus['application'] }}</span>
                        </li>
                        <li class="d-flex align-items-start justify-content-between gap-12 mb-16">
                            <span class="text-sm">TikTok account</span>
                            <span class="badge {{ $connected ? 'bg-success-focus text-success-main' : 'bg-neutral-200 text-secondary-light' }} radius-8 flex-shrink-0">{{ $setupStatus['account'] }}</span>
                        </li>
                        <li class="d-flex align-items-start justify-content-between gap-12 mb-16">
                            <span class="text-sm">Advertiser account</span>
                            <span class="badge {{ $connected ? 'bg-success-focus text-success-main' : 'bg-neutral-200 text-secondary-light' }} radius-8 flex-shrink-0">{{ $setupStatus['advertiser'] }}</span>
                        </li>
                        <li class="d-flex align-items-start justify-content-between gap-12 mb-16">
                            <span class="text-sm">Lead forms</span>
                            <span class="badge {{ ($formsSynced ?? false) ? 'bg-success-focus text-success-main' : 'bg-neutral-200 text-secondary-light' }} radius-8 flex-shrink-0">{{ $setupStatus['forms'] }}</span>
                        </li>
                        <li class="d-flex align-items-start justify-content-between gap-12">
                            <span class="text-sm">Lead delivery</span>
                            <span class="badge {{ ($webhookSubscribed ?? false) ? 'bg-success-focus text-success-main' : (($connected ?? false) ? 'bg-warning-focus text-warning-main' : 'bg-neutral-200 text-secondary-light') }} radius-8 flex-shrink-0">{{ $setupStatus['delivery'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            @if($connected)
                <div class="card radius-12 shadow-2 border-0 mb-24">
                    <div class="card-body p-24">
                        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-16 mb-16">
                            <div>
                                <h6 class="mb-8">Lead delivery</h6>
                                @if($webhookSubscribed)
                                    <p class="text-sm text-secondary-light mb-0">
                                        TikTok is sending Instant Form leads to Enrolliq automatically.
                                    </p>
                                @else
                                    <p class="text-sm text-secondary-light mb-12">
                                        Lead delivery is not enabled yet. Click the button to connect instant lead sync for this advertiser account.
                                    </p>
                                @endif
                            </div>
                            @can('manage integrations')
                                @if(! $webhookSubscribed)
                                    <form method="POST" action="{{ route('admin.integrations.tiktok.register-webhook') }}" class="flex-shrink-0">
                                        @csrf
                                        <button type="submit" class="btn btn-primary-600 radius-8">Enable lead delivery</button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            @endif

            <div class="card radius-12 shadow-2 border-0 mb-24">
                <div class="card-body p-24">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-16 mb-16">
                        <h6 class="mb-0">Recent TikTok leads</h6>
                        @can('manage integrations')
                            @if($connected && ($eligibleReprocessCount ?? 0) > 0)
                                <form method="POST" action="{{ route('admin.integrations.tiktok.reprocess-pending') }}" class="flex-shrink-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8">Reprocess pending ({{ $eligibleReprocessCount }})</button>
                                </form>
                            @endif
                        @endcan
                    </div>
                    @if(($recentSubmissions ?? collect())->isEmpty())
                        <div class="text-center py-32 text-secondary-light">
                            <iconify-icon icon="solar:inbox-in-linear" width="40" class="mb-12"></iconify-icon>
                            <p class="fw-medium text-neutral-700 mb-8">No TikTok leads received yet</p>
                            <p class="text-sm mb-0">Leads will appear here after TikTok is connected and a Lead Generation Instant Form is submitted.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Lead</th>
                                        <th>Form</th>
                                        <th>Received</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentSubmissions as $submission)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">{{ $submission->displayName() }}</div>
                                                @if($submission->error_message && $submission->status->value === 'failed')
                                                    <p class="text-xs text-danger-main mb-0 mt-4">{{ $submission->error_message }}</p>
                                                @endif
                                            </td>
                                            <td class="text-sm">{{ $submission->formDisplayName() }}</td>
                                            <td class="text-sm">{{ $submission->received_at?->timezone(config('app.timezone'))->toDayDateTimeString() ?? '—' }}</td>
                                            <td>
                                                <span class="badge {{ $submission->status->badgeClass() }} radius-8">{{ $submission->status->label() }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex flex-wrap justify-content-end gap-8">
                                                    @if($submission->lead_id)
                                                        @can('view leads')
                                                            <a href="{{ route('admin.crm.leads.show', $submission->lead_id) }}" class="btn btn-sm btn-outline-primary-600 radius-8">View lead</a>
                                                        @endcan
                                                    @endif
                                                    @can('manage integrations')
                                                        @if($submission->status->canReprocess())
                                                            <form method="POST" action="{{ route('admin.integrations.tiktok.submissions.reprocess', $submission) }}">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8">Reprocess</button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card radius-12 shadow-2 border-0">
                <div class="card-body p-24">
                    <h6 class="mb-8">Working with TikTok Ads Manager</h6>
                    <p class="text-sm text-secondary-light mb-0">
                        Create and run campaigns in TikTok Ads Manager as usual. Enrolliq imports leads from TikTok Lead Generation Instant Forms into your CRM — it does not replace Ads Manager.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
