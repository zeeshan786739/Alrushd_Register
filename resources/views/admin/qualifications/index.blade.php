@extends('admin.layouts.app')

@section('title') Qualifications @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Qualifications',
    'subtitle' => 'Define qualification types for staff and applications.',
    'breadcrumbs' => [
        ['label' => 'HR'],
        ['label' => 'Qualifications'],
    ],
    'icon' => 'solar:diploma-linear',
    'routePrefix' => 'admin.qualifications',
    'searchPlaceholder' => 'Search qualifications…',
    'actions' => [[
        'label' => 'Add Qualifications',
        'url' => route('admin.qualifications.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
