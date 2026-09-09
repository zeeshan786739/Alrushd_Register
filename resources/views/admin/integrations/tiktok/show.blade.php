@extends('admin.layouts.app')
@section('title', 'TikTok Lead Ads')
@section('content')
<div class="dashboard-main-body" id="integrations-workspace-page">
@include('admin.integrations.partials.shell', [
    'activeTab' => 'tiktok',
    'shellTitle' => 'TikTok Lead Ads',
    'shellSubtitle' => 'Connect a TikTok Ads account and send Instant Form leads into CRM',
    'shellBreadcrumbs' => [
        ['label' => 'Integrations', 'url' => route('admin.integrations.hub')],
        ['label' => 'TikTok'],
    ],
    'hideFlash' => true,
    'metrics' => [
        ['label' => 'Account', 'value' => $connected ? 'Connected' : 'Not connected'],
        ['label' => 'Forms', 'value' => number_format($formMappings->count())],
        ['label' => 'Delivery', 'value' => ($webhookSubscribed ?? false) ? 'Active' : 'Pending'],
    ],
])

<div class="int-page-body">
    <div class="int-panel mb-16">
        <div class="int-hero">
            <div class="int-hero__identity">
                <span class="int-hero__icon"><iconify-icon icon="logos:tiktok-icon" width="28"></iconify-icon></span>
                <div>
                    <div class="int-hero__badges">
                        <h2 class="int-hero__title mb-0">TikTok Lead Ads</h2>
                        @if($connected)
                            <span class="badge {{ $connection->status->badgeClass() }} radius-8">{{ $connection->status->label() }}</span>
                        @elseif($pendingAdvertiserSelection)
                            <span class="int-status-badge int-status-badge--warning">Select account</span>
                        @else
                            <span class="int-status-badge int-status-badge--neutral">Not Connected</span>
                        @endif
                    </div>
                    @if($connected)
                        <p class="int-hero__text mb-4"><strong>Advertiser account:</strong> {{ $connection->external_account_name ?? 'Connected' }}</p>
                        @if($connectedAt)
                            <p class="int-hero__text mb-0"><strong>Connected:</strong> {{ $connectedAt }}</p>
                        @endif
                    @else
                        <p class="int-hero__text mb-0">Connect a TikTok Ads account and automatically send leads from TikTok Lead Generation Instant Forms into your CRM.</p>
                    @endif
                </div>
            </div>
            <div class="flex-shrink-0">
                @can('manage integrations')
                    @if($pendingAdvertiserSelection)
                        <a href="{{ route('admin.integrations.tiktok.connect') }}" class="btn btn-outline-primary-600 radius-8">Authorize a different account</a>
                    @elseif($connected)
                        <a href="{{ route('admin.integrations.tiktok.connect') }}" class="btn btn-outline-primary-600 radius-8">Reconnect TikTok</a>
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

    @if($pendingAdvertiserSelection)
        <div class="int-panel mb-16">
            <div class="int-panel__head">
                <div>
                    <h3 class="int-panel__title">Select TikTok Ads Account</h3>
                    <p class="int-panel__sub">Choose the TikTok Ads Manager account whose Lead Generation forms should connect to this organization.</p>
                </div>
            </div>
            <div class="int-panel__body">
                <div class="int-advertiser-grid">
                    @foreach($pendingAdvertisers as $advertiser)
                        <div class="int-advertiser-card">
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
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="int-panel mb-16">
        <div class="int-panel__head">
            <div>
                <h3 class="int-panel__title">How setup works</h3>
                <p class="int-panel__sub">Each school connects its own TikTok Ads account. Campaigns stay in TikTok Ads Manager — Enrolliq imports the leads.</p>
            </div>
        </div>
        <div class="int-panel__body">
            <div class="int-steps-grid">
                @foreach($setupSteps as $step)
                    <div class="int-step-card">
                        <div class="int-step-card__badges">
                            <span class="int-step-card__num">{{ $step['n'] }}</span>
                            @if($step['state'] === 'complete')
                                <span class="int-status-badge int-status-badge--success">Complete</span>
                            @elseif($step['state'] === 'current')
                                <span class="int-status-badge int-status-badge--warning">In progress</span>
                            @else
                                <span class="int-status-badge int-status-badge--neutral">Pending</span>
                            @endif
                        </div>
                        <p class="int-step-card__title">{{ $step['title'] }}</p>
                        <p class="int-step-card__text">{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="int-feature-grid mb-16">
        <div class="int-feature-card">
            <span class="int-feature-card__icon"><iconify-icon icon="solar:refresh-circle-linear"></iconify-icon></span>
            <h4 class="int-feature-card__title">Automatic Lead Sync</h4>
            <p class="int-feature-card__text">Leads from TikTok Instant Forms flow into this school’s CRM without manual export.</p>
        </div>
        <div class="int-feature-card">
            <span class="int-feature-card__icon"><iconify-icon icon="solar:document-text-linear"></iconify-icon></span>
            <h4 class="int-feature-card__title">Instant Form Mapping</h4>
            <p class="int-feature-card__text">Map each TikTok form to the right CRM source, assignee, and fields.</p>
        </div>
        <div class="int-feature-card">
            <span class="int-feature-card__icon"><iconify-icon icon="solar:lock-keyhole-linear"></iconify-icon></span>
            <h4 class="int-feature-card__title">Secure Account Connection</h4>
            <p class="int-feature-card__text">This school connects its own TikTok Ads account. Other organizations cannot see it.</p>
        </div>
        <div class="int-feature-card">
            <span class="int-feature-card__icon"><iconify-icon icon="solar:user-plus-linear"></iconify-icon></span>
            <h4 class="int-feature-card__title">CRM Lead Creation</h4>
            <p class="int-feature-card__text">Qualified submissions become CRM leads you can follow up on in Enrolliq.</p>
        </div>
    </div>

    @if($connected)
        <div class="int-panel mb-16">
            <div class="int-panel__head">
                <div>
                    <h3 class="int-panel__title">TikTok Lead Forms</h3>
                    <p class="int-panel__sub">
                        Instant Forms from this school’s connected TikTok Ads account.
                        @if($formsLastSyncedAt)
                            Last synced {{ $formsLastSyncedAt }}.
                        @endif
                    </p>
                </div>
                @can('manage integrations')
                    <form method="POST" action="{{ route('admin.integrations.tiktok.sync-forms') }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="btn btn-primary-600 radius-8">Sync Lead Forms</button>
                    </form>
                @endcan
            </div>
            <div class="int-panel__body int-panel__body--flush">
                @if($formMappings->isEmpty())
                    <div class="crm-leads-list-empty">
                        <iconify-icon icon="solar:document-linear"></iconify-icon>
                        <strong>No TikTok Lead Forms found</strong>
                        <span>Create a Lead Generation Instant Form in TikTok Ads Manager, then sync again.</span>
                    </div>
                @else
                    <div class="crm-leads-table">
                        <div class="crm-leads-table__head crm-leads-table__head--tiktok-forms" aria-hidden="true">
                            <span>Form</span><span>Status</span><span>Mapping</span><span>Assigned to</span><span>Lead source</span><span></span>
                        </div>
                        <div class="crm-leads-list">
                            @foreach($formMappings as $form)
                                @include('admin.integrations.partials.tiktok-form-row', ['form' => $form])
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="int-layout">
        <aside class="int-layout__stack">
            <div class="int-panel">
                <div class="int-panel__head">
                    <div>
                        <h3 class="int-panel__title">Setup status</h3>
                        <p class="int-panel__sub">Integration readiness at a glance</p>
                    </div>
                </div>
                <div class="int-panel__body">
                    <ul class="int-meta-list">
                        <li><span>Integration service</span><span class="badge {{ $configOk ? 'bg-success-focus text-success-main' : 'bg-neutral-200 text-secondary-light' }} radius-8">{{ $setupStatus['application'] }}</span></li>
                        <li><span>TikTok account</span><span class="badge {{ $connected ? 'bg-success-focus text-success-main' : 'bg-neutral-200 text-secondary-light' }} radius-8">{{ $setupStatus['account'] }}</span></li>
                        <li><span>Advertiser account</span><span class="badge {{ $connected ? 'bg-success-focus text-success-main' : 'bg-neutral-200 text-secondary-light' }} radius-8">{{ $setupStatus['advertiser'] }}</span></li>
                        <li><span>Lead forms</span><span class="badge {{ ($formsSynced ?? false) ? 'bg-success-focus text-success-main' : 'bg-neutral-200 text-secondary-light' }} radius-8">{{ $setupStatus['forms'] }}</span></li>
                        <li><span>Lead delivery</span><span class="badge {{ ($webhookSubscribed ?? false) ? 'bg-success-focus text-success-main' : (($connected ?? false) ? 'bg-warning-focus text-warning-main' : 'bg-neutral-200 text-secondary-light') }} radius-8">{{ $setupStatus['delivery'] }}</span></li>
                    </ul>
                </div>
            </div>
        </aside>

        <div class="int-layout__stack">
            @if($connected)
                <div class="int-panel">
                    <div class="int-panel__head">
                        <div>
                            <h3 class="int-panel__title">Lead delivery</h3>
                            @if($webhookSubscribed)
                                <p class="int-panel__sub">TikTok is sending Instant Form leads to Enrolliq automatically.</p>
                            @else
                                <p class="int-panel__sub">Lead delivery is not enabled yet. Click the button to connect instant lead sync for this advertiser account.</p>
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
            @endif

            <div class="int-panel">
                <div class="int-panel__head">
                    <div>
                        <h3 class="int-panel__title">Recent TikTok leads</h3>
                        <p class="int-panel__sub">Latest Instant Form submissions</p>
                    </div>
                    @can('manage integrations')
                        @if($connected && ($eligibleReprocessCount ?? 0) > 0)
                            <form method="POST" action="{{ route('admin.integrations.tiktok.reprocess-pending') }}" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8">Reprocess pending ({{ $eligibleReprocessCount }})</button>
                            </form>
                        @endif
                    @endcan
                </div>
                <div class="int-panel__body int-panel__body--flush">
                    @if(($recentSubmissions ?? collect())->isEmpty())
                        <div class="crm-leads-list-empty">
                            <iconify-icon icon="solar:inbox-in-linear"></iconify-icon>
                            <strong>No TikTok leads received yet</strong>
                            <span>Leads will appear here after TikTok is connected and a Lead Generation Instant Form is submitted.</span>
                        </div>
                    @else
                        <div class="crm-leads-table">
                            <div class="crm-leads-table__head crm-leads-table__head--tiktok-submissions" aria-hidden="true">
                                <span>Lead</span><span>Form</span><span>Received</span><span>Status</span><span></span>
                            </div>
                            <div class="crm-leads-list">
                                @foreach($recentSubmissions as $submission)
                                    @include('admin.integrations.partials.submission-row', ['submission' => $submission, 'mode' => 'tiktok'])
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="int-panel">
                <div class="int-panel__body">
                    <div class="int-info-note">
                        <iconify-icon icon="solar:info-circle-linear"></iconify-icon>
                        <span><strong>Working with TikTok Ads Manager</strong> — Create and run campaigns in TikTok Ads Manager as usual. Enrolliq imports leads from TikTok Lead Generation Instant Forms into your CRM — it does not replace Ads Manager.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
