@extends('admin.layouts.app')
@section('title', 'TikTok Lead Generation')
@section('content')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'TikTok Lead Generation',
        'subtitle' => 'Coming soon — import TikTok ad leads into your CRM',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'Integrations', 'url' => route('admin.integrations.hub')],
            ['label' => 'TikTok'],
        ],
    ])

    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-40 text-center">
            <iconify-icon icon="logos:tiktok-icon" width="48" class="mb-16"></iconify-icon>
            <h5 class="mb-12">TikTok integration is on the roadmap</h5>
            <p class="text-secondary-light mb-24 max-w-600 mx-auto">
                Facebook Lead Ads is live. TikTok will use the same Integration Hub pattern:
                connect your ad account, map lead forms, and auto-create CRM leads per school.
            </p>
            <a href="{{ route('admin.integrations.facebook.show') }}" class="btn btn-primary-600 radius-8">
                Set up Facebook first
            </a>
        </div>
    </div>
</div>
@endsection
