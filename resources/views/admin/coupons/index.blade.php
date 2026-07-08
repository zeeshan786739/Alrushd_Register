@extends('admin.layouts.app')

@section('title') Coupons @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Coupons',
    'subtitle' => 'Create and manage discount coupons.',
    'breadcrumbs' => [
        ['label' => 'Billing'],
        ['label' => 'Coupons'],
    ],
    'icon' => 'solar:ticket-linear',
    'routePrefix' => 'admin.coupons',
    'searchPlaceholder' => 'Search coupons…',
    'actions' => [[
        'label' => 'Add Coupons',
        'url' => route('admin.coupons.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
