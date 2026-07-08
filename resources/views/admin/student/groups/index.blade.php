@extends('admin.layouts.app')

@section('title') Student Groups @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Student Groups',
    'subtitle' => 'Organize students into groups for scheduling and reporting.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Student Groups'],
    ],
    'icon' => 'solar:users-group-rounded-linear',
    'routePrefix' => 'admin.student-groups',
    'searchPlaceholder' => 'Search student groups…',
    'actions' => [[
        'label' => 'Add Student Groups',
        'url' => route('admin.student-groups.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
