@extends('admin.layouts.app')

@section('title') Groups @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Groups',
    'subtitle' => 'Organize classes and cohorts into groups.',
    'breadcrumbs' => [
        ['label' => 'Academics'],
        ['label' => 'Groups'],
    ],
    'icon' => 'solar:users-group-rounded-linear',
    'routePrefix' => 'admin.groups',
    'searchPlaceholder' => 'Search groups…',
    'actions' => [[
        'label' => 'Add Groups',
        'url' => route('admin.groups.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
