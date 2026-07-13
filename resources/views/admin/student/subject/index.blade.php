@extends('admin.layouts.app')

@section('title') Subjects @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Subjects',
    'subtitle' => 'Maintain subject catalog entries for student enrollment.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Subjects'],
    ],
    'icon' => 'solar:book-linear',
    'routePrefix' => 'admin.student-subject',
    'searchPlaceholder' => 'Search subjects…',
    'actions' => [[
        'label' => 'Add Subjects',
        'url' => route('admin.student-subject.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
