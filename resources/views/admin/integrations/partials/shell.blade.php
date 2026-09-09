@php
    $activeTab = $activeTab ?? 'hub';
    $metrics = $metrics ?? [];
@endphp

@once
    @include('admin.crm.partials.styles')
    @include('admin.crm.partials.workspace-shell')
    @include('admin.integrations.partials.premium-styles')
@endonce

@include('admin.partials.page-header', [
    'title' => $shellTitle ?? 'Integrations',
    'subtitle' => $shellSubtitle ?? 'Connect ad platforms and import leads into your CRM',
    'showBreadcrumb' => true,
    'breadcrumbs' => $shellBreadcrumbs ?? [['label' => 'Integrations']],
    'actions' => $shellActions ?? [],
    'hideFlash' => $hideFlash ?? false,
])

<div class="int-workspace crm-workspace-shell">
    @if(empty($compact))
        <div class="crm-metrics-strip" aria-label="Integrations metrics">
            <div class="crm-metrics-strip__items">
                <span class="crm-metrics-strip__hint">Integrations workspace</span>
                @foreach($metrics as $index => $metric)
                    @if($index > 0)
                        <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    @endif
                    <span class="crm-metrics-strip__item">
                        <span class="crm-metrics-strip__label">{{ $metric['label'] }}</span>
                        <strong>{{ $metric['value'] }}</strong>
                    </span>
                @endforeach
            </div>
        </div>

        <div class="int-tabs-workspace">
            <div class="int-tabs-workspace__head">
                <h2 class="int-tabs-workspace__title">Workspace sections</h2>
                <p class="int-tabs-workspace__sub">Overview, Facebook Lead Ads, and TikTok Lead Ads</p>
            </div>
            @include('admin.integrations.partials.module-nav', ['activeTab' => $activeTab])
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 m-3 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger bg-danger-focus text-danger-main border-0 radius-8 m-3 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:close-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
            <span>{{ session('error') }}</span>
        </div>
    @endif
</div>
