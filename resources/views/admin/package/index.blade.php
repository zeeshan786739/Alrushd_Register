@extends('admin.layouts.app')

@section('title') Packages @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Packages',
    'subtitle' => 'Configure service packages and pricing tiers.',
    'breadcrumbs' => [
        ['label' => 'Billing'],
        ['label' => 'Packages'],
    ],
    'icon' => 'solar:box-linear',
    'routePrefix' => 'admin.package',
    'searchPlaceholder' => 'Search packages…',
    'actions' => [[
        'label' => 'Add Packages',
        'url' => route('admin.package.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
