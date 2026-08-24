@extends('admin.layouts.app')

@section('title', 'Enrollment Setup')

@section('css')
<link rel="stylesheet" href="{{ asset('admin/assets/css/enrollment-setup.css') }}">
@endsection

@section('content')
@include('admin.partials.page-header', [
    'title' => 'Enrollment Setup',
    'subtitle' => 'Manage the dropdown options and catalog your enrollment forms use. Defaults are created for your school — edit, add, or remove anything.',
    'breadcrumbs' => [
        ['label' => 'Forms & Intake'],
        ['label' => 'Enrollment Setup'],
    ],
])

@php
    $allTypes = [];
    foreach ($sections as $group) {
        foreach ($group['types'] as $typeKey => $typeData) {
            $allTypes[$typeKey] = $typeData;
        }
    }
@endphp

<div class="enroll-setup" id="enrollmentSetup" data-initial-tab="{{ $activeType }}">
    <aside class="enroll-setup__nav card radius-12 border-0 shadow-sm" role="tablist" aria-label="Catalog sections">
        <div class="enroll-setup__nav-head">
            <iconify-icon icon="solar:settings-minimalistic-linear"></iconify-icon>
            <span>Catalog sections</span>
        </div>
        @foreach($sections as $groupKey => $group)
            @if(empty($group['types'])) @continue @endif
            <p class="enroll-setup__nav-group">{{ $group['label'] }}</p>
            @foreach($group['types'] as $typeKey => $typeData)
            @php $cfg = $typeData['config']; @endphp
            <button type="button"
                    role="tab"
                    id="tab-{{ $typeKey }}"
                    class="enroll-setup__nav-item {{ ($activeType === $typeKey) ? 'is-active' : '' }}"
                    data-tab="{{ $typeKey }}"
                    aria-selected="{{ ($activeType === $typeKey) ? 'true' : 'false' }}"
                    aria-controls="panel-{{ $typeKey }}">
                <iconify-icon icon="{{ $cfg['icon'] ?? 'solar:widget-linear' }}"></iconify-icon>
                <span>{{ $cfg['label'] }}</span>
                <em>{{ $typeData['items']->count() }}</em>
            </button>
            @endforeach
        @endforeach

        <div class="enroll-setup__nav-extra">
            <p class="enroll-setup__nav-group">Advanced</p>
            @canany(['create course','edit course','view course'])
            <a href="{{ route('admin.student-course.index') }}" class="enroll-setup__nav-item enroll-setup__nav-item--link">
                <iconify-icon icon="solar:notebook-linear"></iconify-icon>
                <span>Course builder</span>
                <em>{{ $courseCount }}</em>
            </a>
            @endcanany
            @canany(['edit terms_condition','view terms_condition'])
            <a href="{{ route('admin.terms.index') }}" class="enroll-setup__nav-item enroll-setup__nav-item--link">
                <iconify-icon icon="solar:document-text-linear"></iconify-icon>
                <span>Terms &amp; conditions</span>
            </a>
            @endcanany
        </div>
    </aside>

    <div class="enroll-setup__main enroll-setup__panels">
        @forelse($allTypes as $typeKey => $typeData)
            @include('admin.enrollment-setup.partials.panel', [
                'typeKey' => $typeKey,
                'typeData' => $typeData,
                'isActive' => $activeType === $typeKey,
            ])
        @empty
        <div class="card radius-12 border-0 shadow-sm enroll-panel enroll-panel--empty">
            <p class="mb-0 text-secondary-light">You do not have permission to manage enrollment catalog items.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('admin/assets/js/enrollment-setup.js') }}"></script>
@endsection
