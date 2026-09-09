@extends('admin.layouts.app')

@section('title', 'Enrollment Setup')

@section('content')
@once
    @include('admin.crm.partials.styles')
    @include('admin.crm.partials.workspace-shell')
    @include('admin.enrollment-setup.partials.premium-styles')
@endonce

@php
    $allTypes = [];
    foreach ($sections as $group) {
        foreach ($group['types'] as $typeKey => $typeData) {
            $allTypes[$typeKey] = $typeData;
        }
    }
    $totalItems = collect($allTypes)->sum(fn ($type) => $type['items']->count());
@endphp

<div class="dashboard-main-body" id="enrollment-setup-page">
    @include('admin.partials.page-header', [
        'title' => 'Enrollment Setup',
        'subtitle' => 'Manage the dropdown options and catalog your enrollment forms use. Defaults are created for your school — edit, add, or remove anything.',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'Forms & Intake'],
            ['label' => 'Enrollment Setup'],
        ],
    ])

    <div class="enroll-workspace crm-workspace-shell">
        <div class="crm-metrics-strip" aria-label="Enrollment setup metrics">
            <div class="crm-metrics-strip__items">
                <span class="crm-metrics-strip__hint">Enrollment catalog workspace</span>
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item">
                    <span class="crm-metrics-strip__label">Catalog types</span>
                    <strong>{{ count($allTypes) }}</strong>
                </span>
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item">
                    <span class="crm-metrics-strip__label">Total items</span>
                    <strong>{{ number_format($totalItems) }}</strong>
                </span>
                <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                <span class="crm-metrics-strip__item">
                    <span class="crm-metrics-strip__label">Courses</span>
                    <strong>{{ number_format($courseCount) }}</strong>
                </span>
            </div>
        </div>
    </div>

    <div class="enroll-page-body" id="enrollmentSetup" data-initial-tab="{{ $activeType }}">
        <aside class="enroll-nav-panel" role="tablist" aria-label="Catalog sections">
            <div class="enroll-nav-panel__head">
                <iconify-icon icon="solar:settings-minimalistic-linear"></iconify-icon>
                <span>Catalog sections</span>
            </div>
            @foreach($sections as $groupKey => $group)
                @if(empty($group['types'])) @continue @endif
                <p class="enroll-nav-group">{{ $group['label'] }}</p>
                @foreach($group['types'] as $typeKey => $typeData)
                @php $cfg = $typeData['config']; @endphp
                <button type="button"
                        role="tab"
                        id="tab-{{ $typeKey }}"
                        class="enroll-nav-item {{ ($activeType === $typeKey) ? 'is-active' : '' }}"
                        data-tab="{{ $typeKey }}"
                        aria-selected="{{ ($activeType === $typeKey) ? 'true' : 'false' }}"
                        aria-controls="panel-{{ $typeKey }}">
                    <iconify-icon icon="{{ $cfg['icon'] ?? 'solar:widget-linear' }}"></iconify-icon>
                    <span>{{ $cfg['label'] }}</span>
                    <em>{{ $typeData['items']->count() }}</em>
                </button>
                @endforeach
            @endforeach

            <div class="enroll-nav-extra">
                <p class="enroll-nav-group">Advanced</p>
                @canany(['create course','edit course','view course'])
                <a href="{{ route('admin.student-course.index') }}" class="enroll-nav-item enroll-nav-item--link">
                    <iconify-icon icon="solar:notebook-linear"></iconify-icon>
                    <span>Course builder</span>
                    <em>{{ $courseCount }}</em>
                </a>
                @endcanany
                @canany(['edit terms_condition','view terms_condition'])
                <a href="{{ route('admin.terms.index') }}" class="enroll-nav-item enroll-nav-item--link">
                    <iconify-icon icon="solar:document-text-linear"></iconify-icon>
                    <span>Terms &amp; conditions</span>
                </a>
                @endcanany
            </div>
        </aside>

        <div class="enroll-main enroll-setup__panels">
            @forelse($allTypes as $typeKey => $typeData)
                @include('admin.enrollment-setup.partials.panel', [
                    'typeKey' => $typeKey,
                    'typeData' => $typeData,
                    'isActive' => $activeType === $typeKey,
                ])
            @empty
            <div class="enroll-panel is-visible enroll-panel--empty">
                <div class="crm-leads-list-empty">
                    <strong>No permission</strong>
                    <span>You do not have permission to manage enrollment catalog items.</span>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('admin/assets/js/enrollment-setup.js') }}"></script>
@endsection
