@extends('admin.layouts.app')

@section('title') Create Permission @endsection

@section('content')
@include('admin.role-permission.partials.shell', [
    'activeTab' => 'permissions',
    'stats' => $stats,
    'compact' => true,
    'shellTitle' => 'Create a permission',
    'shellSubtitle' => 'Answer three simple questions — we’ll build the correct permission name for you.',
    'shellActions' => [[
        'label' => 'Back to permissions',
        'url' => route('admin.permissions.index'),
        'class' => 'btn-outline-neutral-500 radius-8 px-20 py-11',
        'icon' => 'solar:alt-arrow-left-linear',
    ]],
])

<form class="needs-validation" novalidate action="{{ route('admin.permissions.store') }}" method="POST">
    @csrf
    @include('admin.role-permission.partials.permission-builder', [
        'existingNames' => $existingNames,
        'builderActions' => $builderActions,
        'builderModules' => $builderModules,
        'builderPresets' => $builderPresets,
    ])
</form>
@endsection

@section('script')
@include('admin.role-permission.partials.permission-builder-script')
@endsection
