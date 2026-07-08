@extends('admin.layouts.app')

@section('title') Academic Year @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Academic Year',
    'subtitle' => 'Set academic year labels used in student records.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Academic Year'],
    ],
    'icon' => 'solar:calendar-minimalistic-linear',
    'routePrefix' => 'admin.student-years',
    'searchPlaceholder' => 'Search academic year…',
    'actions' => [[
        'label' => 'Add Academic Year',
        'url' => route('admin.student-years.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
