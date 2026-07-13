@extends('admin.layouts.app')

@section('title') Subjects @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Subjects',
    'subtitle' => 'Maintain subject catalog for courses and scheduling.',
    'breadcrumbs' => [
        ['label' => 'Academics'],
        ['label' => 'Subjects'],
    ],
    'icon' => 'solar:book-linear',
    'routePrefix' => 'admin.subjects',
    'searchPlaceholder' => 'Search subjects…',
    'actions' => [[
        'label' => 'Add Subjects',
        'url' => route('admin.subjects.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
