@extends('admin.layouts.app')
@section('title', 'Integrations')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Integrations',
        'subtitle' => 'Connect ad platforms and import leads into your CRM',
        'showBreadcrumb' => true,
        'breadcrumbs' => [['label' => 'Integrations']],
    ])

    @if(session('success'))<div class="alert alert-success radius-8">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger radius-8">{{ session('error') }}</div>@endif

    <div class="row g-3 mb-24">
        <div class="col-md-4">
            @include('admin.partials.dashboard-stat-card', [
                'label' => 'Facebook',
                'value' => $stats['facebook_connected'] ? 'Connected' : 'Not connected',
                'icon' => 'logos:facebook',
                'tone' => $stats['facebook_connected'] ? 'green' : 'amber',
            ])
        </div>
        <div class="col-md-4">
            @include('admin.partials.dashboard-stat-card', [
                'label' => 'Facebook Leads',
                'value' => $stats['facebook_leads_total'],
                'icon' => 'solar:inbox-in-linear',
                'tone' => 'navy',
            ])
        </div>
        <div class="col-md-4">
            @include('admin.partials.dashboard-stat-card', [
                'label' => 'Needs Mapping',
                'value' => $stats['facebook_leads_unmapped'],
                'icon' => 'solar:settings-linear',
                'tone' => $stats['facebook_leads_unmapped'] > 0 ? 'amber' : 'green',
            ])
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <div class="d-flex align-items-start justify-content-between gap-12 mb-16">
                        <div class="d-flex align-items-center gap-12">
                            <span class="w-48-px h-48-px rounded-circle d-flex align-items-center justify-content-center bg-primary-50">
                                <iconify-icon icon="logos:facebook" width="28"></iconify-icon>
                            </span>
                            <div>
                                <h6 class="mb-4">Facebook Lead Ads</h6>
                                <p class="text-sm text-secondary-light mb-0">Import leads from Facebook &amp; Instagram ads directly into CRM.</p>
                            </div>
                        </div>
                        @if($facebookConnection?->isConnected())
                            <span class="badge {{ $facebookConnection->status->badgeClass() }} radius-8">{{ $facebookConnection->status->label() }}</span>
                        @else
                            <span class="badge bg-neutral-200 text-secondary-light radius-8">Not connected</span>
                        @endif
                    </div>

                    @if($facebookConnection?->isConnected())
                        <p class="text-sm mb-8"><strong>Page:</strong> {{ $facebookConnection->external_account_name }}</p>
                        <p class="text-sm mb-16 text-secondary-light">
                            Last webhook: {{ $facebookConnection->last_webhook_at?->diffForHumans() ?? 'Never' }}
                        </p>
                    @endif

            <a href="{{ route('admin.integrations.facebook.show') }}" class="btn btn-primary-600 radius-8">
                {{ $facebookConnection?->isConnected() ? 'Manage Facebook' : 'Connect Facebook' }}
            </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card radius-12 shadow-2 border-0 h-100">
                <div class="card-body p-24">
                    <div class="d-flex align-items-start justify-content-between gap-12 mb-16">
                        <div class="d-flex align-items-center gap-12">
                            <span class="w-48-px h-48-px rounded-circle d-flex align-items-center justify-content-center bg-neutral-100">
                                <iconify-icon icon="logos:tiktok-icon" width="28"></iconify-icon>
                            </span>
                            <div>
                                <h6 class="mb-4">TikTok Lead Ads</h6>
                                <p class="text-sm text-secondary-light mb-0">Import leads from TikTok Lead Generation Instant Forms into CRM.</p>
                            </div>
                        </div>
                        @if($tiktokConnection?->isConnected())
                            <span class="badge {{ $tiktokConnection->status->badgeClass() }} radius-8">{{ $tiktokConnection->status->label() }}</span>
                        @else
                            <span class="badge bg-neutral-200 text-secondary-light radius-8">Not connected</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.integrations.tiktok.show') }}" class="btn btn-primary-600 radius-8">
                        {{ $tiktokConnection?->isConnected() ? 'Manage TikTok' : 'Set up TikTok' }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($recentFacebookLeads->isNotEmpty())
    <div class="card radius-12 shadow-2 border-0 mt-24">
        <div class="card-body p-24">
            <h6 class="mb-16">Recent Facebook leads</h6>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Lead ID</th>
                            <th>Form</th>
                            <th>Status</th>
                            <th>Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentFacebookLeads as $submission)
                        <tr>
                            <td><code>{{ $submission->meta_leadgen_id }}</code></td>
                            <td>{{ $submission->meta_form_id ?? '—' }}</td>
                            <td><span class="badge bg-neutral-200 text-secondary-light radius-8">{{ ucfirst($submission->status->value) }}</span></td>
                            <td>{{ $submission->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
