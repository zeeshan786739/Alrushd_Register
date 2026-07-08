@extends('admin.layouts.app')

@section('title') Admission Date @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Admission Date',
    'subtitle' => 'Configure admission date options for enrollment workflows.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Admission Date'],
    ],
    'icon' => 'solar:calendar-linear',
    'routePrefix' => 'admin.admission-date',
    'searchPlaceholder' => 'Search admission date…',
    'actions' => [[
        'label' => 'Add Admission Date',
        'url' => route('admin.admission-date.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
