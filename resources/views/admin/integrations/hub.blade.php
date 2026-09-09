@extends('admin.layouts.app')
@section('title', 'Integrations')
@section('content')
<div class="dashboard-main-body" id="integrations-workspace-page">
@include('admin.integrations.partials.shell', [
    'activeTab' => 'hub',
    'shellTitle' => 'Integrations',
    'shellSubtitle' => 'Connect ad platforms and import leads into your CRM',
    'shellBreadcrumbs' => [['label' => 'Integrations']],
    'hideFlash' => true,
    'metrics' => [
        ['label' => 'Facebook', 'value' => $stats['facebook_connected'] ? 'Connected' : 'Not connected'],
        ['label' => 'Leads', 'value' => number_format($stats['facebook_leads_total'])],
        ['label' => 'Unmapped', 'value' => number_format($stats['facebook_leads_unmapped'])],
    ],
])

<div class="int-page-body">
    <div class="int-platform-grid mb-16">
        <div class="int-platform-card">
            <div class="int-platform-card__head">
                <div class="int-platform-card__identity">
                    <span class="int-platform-card__icon int-platform-card__icon--facebook"><iconify-icon icon="logos:facebook"></iconify-icon></span>
                    <div>
                        <h3 class="int-platform-card__title">Facebook Lead Ads</h3>
                        <p class="int-platform-card__desc">Import leads from Facebook &amp; Instagram ads directly into CRM.</p>
                    </div>
                </div>
                @if($facebookConnection?->isConnected())
                    <span class="badge {{ $facebookConnection->status->badgeClass() }} radius-8">{{ $facebookConnection->status->label() }}</span>
                @else
                    <span class="int-status-badge int-status-badge--neutral">Not connected</span>
                @endif
            </div>
            @if($facebookConnection?->isConnected())
                <p class="int-platform-card__meta mb-0"><strong>Page:</strong> {{ $facebookConnection->external_account_name }}</p>
                <p class="int-platform-card__meta mb-0">Last lead received: {{ $facebookConnection->last_webhook_at?->diffForHumans() ?? 'None yet' }}</p>
            @endif
            <a href="{{ route('admin.integrations.facebook.show') }}" class="btn btn-primary-600 radius-8 align-self-start">
                {{ $facebookConnection?->isConnected() ? 'Manage Facebook' : 'Connect Facebook' }}
            </a>
        </div>

        <div class="int-platform-card">
            <div class="int-platform-card__head">
                <div class="int-platform-card__identity">
                    <span class="int-platform-card__icon int-platform-card__icon--tiktok"><iconify-icon icon="logos:tiktok-icon"></iconify-icon></span>
                    <div>
                        <h3 class="int-platform-card__title">TikTok Lead Ads</h3>
                        <p class="int-platform-card__desc">Import leads from TikTok Lead Generation Instant Forms into CRM.</p>
                    </div>
                </div>
                @if($tiktokConnection?->isConnected())
                    <span class="badge {{ $tiktokConnection->status->badgeClass() }} radius-8">{{ $tiktokConnection->status->label() }}</span>
                @else
                    <span class="int-status-badge int-status-badge--neutral">Not connected</span>
                @endif
            </div>
            <a href="{{ route('admin.integrations.tiktok.show') }}" class="btn btn-primary-600 radius-8 align-self-start">
                {{ $tiktokConnection?->isConnected() ? 'Manage TikTok' : 'Set up TikTok' }}
            </a>
        </div>
    </div>

    @if($recentFacebookLeads->isNotEmpty())
        <div class="crm-list-shell">
            <div class="crm-leads-toolbar">
                <div class="crm-leads-toolbar__meta">
                    <strong>Recent Facebook leads</strong>
                    <span>Latest imported submissions</span>
                </div>
            </div>
            <div class="crm-leads-table">
                <div class="crm-leads-table__head crm-leads-table__head--hub-submissions" aria-hidden="true">
                    <span>Lead</span><span>Form</span><span>Status</span><span>Received</span>
                </div>
                <div class="crm-leads-list">
                    @foreach($recentFacebookLeads as $submission)
                        @include('admin.integrations.partials.submission-row', ['submission' => $submission, 'mode' => 'hub'])
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
</div>
@endsection
