@extends('admin.layouts.app')

@section('title') Group Years @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Group Years',
    'subtitle' => 'Set academic group year labels.',
    'breadcrumbs' => [
        ['label' => 'Academics'],
        ['label' => 'Group Years'],
    ],
    'icon' => 'solar:calendar-linear',
    'routePrefix' => 'admin.group-years',
    'searchPlaceholder' => 'Search group years…',
    'actions' => [[
        'label' => 'Add Group Years',
        'url' => route('admin.group-years.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
