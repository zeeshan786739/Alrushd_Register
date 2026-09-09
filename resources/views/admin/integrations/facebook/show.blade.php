@extends('admin.layouts.app')
@section('title', 'Facebook Lead Ads')
@section('content')
<div class="dashboard-main-body" id="integrations-workspace-page">
@include('admin.integrations.partials.shell', [
    'activeTab' => 'facebook',
    'shellTitle' => 'Facebook Lead Ads',
    'shellSubtitle' => 'Connect your Facebook Page and map Lead Forms to CRM',
    'shellBreadcrumbs' => [
        ['label' => 'Integrations', 'url' => route('admin.integrations.hub')],
        ['label' => 'Facebook'],
    ],
    'hideFlash' => true,
    'metrics' => [
        ['label' => 'Status', 'value' => $connection->isConnected() ? 'Connected' : 'Not connected'],
        ['label' => 'Forms', 'value' => number_format($connection->formMappings->count())],
        ['label' => 'Pending', 'value' => number_format(($unmappedCount ?? 0) + ($failedCount ?? 0))],
    ],
])

<div class="int-page-body">
    <div class="int-layout">
        <aside class="int-layout__stack">
            <div class="int-panel">
                <div class="int-panel__head">
                    <div>
                        <h3 class="int-panel__title">Connection</h3>
                        <p class="int-panel__sub">Facebook Page and lead delivery for this school</p>
                    </div>
                </div>
                <div class="int-panel__body">
                    @if($connection->isConnected())
                        <div class="mb-12">
                            <span class="badge {{ $connection->status->badgeClass() }} radius-8">{{ $connection->status->label() }}</span>
                        </div>
                        <ul class="int-meta-list mb-16">
                            <li><span>Page</span><strong>{{ $connection->external_account_name }}</strong></li>
                            <li>
                                <span>Lead delivery</span>
                                <strong>
                                    @if($connection->webhook_subscribed_at || $connection->last_webhook_at)
                                        Active — last lead {{ ($connection->last_webhook_at ?? $connection->webhook_subscribed_at)?->diffForHumans() }}
                                    @else
                                        Waiting for first lead
                                    @endif
                                </strong>
                            </li>
                            <li><span>Last sync</span><strong>{{ $connection->last_webhook_at?->diffForHumans() ?? 'No leads yet' }}</strong></li>
                        </ul>
                        @can('manage integrations')
                        <div class="int-panel__actions">
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
        </aside>

        <div class="int-layout__stack">
            <div class="int-panel">
                <div class="int-panel__head">
                    <div>
                        <h3 class="int-panel__title">Lead Form mappings</h3>
                        @if($connection->isConnected())
                            <p class="int-panel__sub">Map each Facebook form to student, teacher, etc.</p>
                        @endif
                    </div>
                </div>
                <div class="int-panel__body int-panel__body--flush">
                    @if($connection->formMappings->isEmpty())
                        <div class="crm-leads-list-empty">
                            <iconify-icon icon="solar:document-linear"></iconify-icon>
                            <strong>No forms mapped yet</strong>
                            <span>Most forms appear after you click <strong>Sync Lead Forms</strong>. You can also add one manually below.</span>
                        </div>
                    @else
                        <div class="crm-leads-table">
                            <div class="crm-leads-table__head crm-leads-table__head--mapping" aria-hidden="true">
                                <span>Facebook form</span><span>Mapping settings</span><span></span>
                            </div>
                            <div class="crm-leads-list">
                                @foreach($connection->formMappings as $mapping)
                                    @include('admin.integrations.partials.facebook-mapping-row', compact('mapping', 'admins'))
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @can('manage integrations')
                    @if($connection->isConnected())
                    <div class="int-panel__body border-top">
                        <p class="text-sm fw-medium mb-8">Add another form</p>
                        <p class="text-sm text-secondary-light mb-12">Most forms appear after you click <strong>Sync Lead Forms</strong>. If one is missing, your marketing team can provide the form reference from Facebook Ads Manager.</p>
                        <form method="POST" action="{{ route('admin.integrations.facebook.register-form') }}" class="int-inline-form">
                            @csrf
                            <div class="int-inline-form__field">
                                <label class="form-label" for="external_form_id">Form reference</label>
                                <input type="text" name="external_form_id" id="external_form_id" class="form-control form-control-sm radius-8" placeholder="Paste form reference from Facebook" required>
                            </div>
                            <div class="int-inline-form__field">
                                <label class="form-label" for="external_form_name">Display name (optional)</label>
                                <input type="text" name="external_form_name" id="external_form_name" class="form-control form-control-sm radius-8" placeholder="e.g. Open day enquiry">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8">Add form</button>
                        </form>
                    </div>
                    @endif
                    @endcan
                </div>
            </div>

            <div class="int-panel">
                <div class="int-panel__head">
                    <div>
                        <h3 class="int-panel__title">Recent imported leads</h3>
                        <p class="int-panel__sub">Latest Facebook submissions received by Enrolliq</p>
                    </div>
                </div>
                <div class="int-panel__body int-panel__body--flush">
                    @if($recentSubmissions->isEmpty())
                        <div class="crm-leads-list-empty">
                            <iconify-icon icon="solar:inbox-in-linear"></iconify-icon>
                            <strong>No Facebook leads received yet</strong>
                            <span>Leads will appear here after your Page is connected and a form is submitted.</span>
                        </div>
                    @else
                        <div class="crm-leads-table">
                            <div class="crm-leads-table__head crm-leads-table__head--facebook-submissions" aria-hidden="true">
                                <span>Status</span><span>Mapping</span><span>CRM Lead</span><span>Received</span><span></span>
                            </div>
                            <div class="crm-leads-list">
                                @foreach($recentSubmissions as $submission)
                                    @include('admin.integrations.partials.submission-row', ['submission' => $submission, 'mode' => 'facebook'])
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('admin.integrations.partials.go-live-checklist')
</div>
</div>
@endsection
