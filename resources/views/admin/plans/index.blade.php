@extends('admin.layouts.app')

@section('title') Plans @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Plans',
    'subtitle' => 'Manage subscription and payment plans.',
    'breadcrumbs' => [
        ['label' => 'Billing'],
        ['label' => 'Plans'],
    ],
    'icon' => 'solar:layers-linear',
    'routePrefix' => 'admin.plans',
    'searchPlaceholder' => 'Search plans…',
    'actions' => [[
        'label' => 'Add Plans',
        'url' => route('admin.plans.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
