@extends('admin.layouts.app')

@section('title') Courses @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Courses',
    'subtitle' => 'Manage course definitions linked to student programs.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Courses'],
    ],
    'icon' => 'solar:square-academic-cap-linear',
    'routePrefix' => 'admin.student-course',
    'searchPlaceholder' => 'Search courses…',
    'actions' => [[
        'label' => 'Add Courses',
        'url' => route('admin.student-course.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
