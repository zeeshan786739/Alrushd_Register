@extends('admin.layouts.app')

@section('title') Schools @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Schools',
    'subtitle' => 'Maintain school names for prior education records.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Schools'],
    ],
    'icon' => 'solar:buildings-linear',
    'routePrefix' => 'admin.student-school',
    'searchPlaceholder' => 'Search schools…',
    'actions' => [[
        'label' => 'Add Schools',
        'url' => route('admin.student-school.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
