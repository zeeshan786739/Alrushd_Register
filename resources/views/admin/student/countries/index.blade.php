@extends('admin.layouts.app')

@section('title') Countries @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Countries',
    'subtitle' => 'Manage countries available for payment and address fields.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Countries'],
    ],
    'icon' => 'solar:global-linear',
    'routePrefix' => 'admin.countries',
    'searchPlaceholder' => 'Search countries…',
    'actions' => [[
        'label' => 'Add Countries',
        'url' => route('admin.countries.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
