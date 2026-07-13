@extends('admin.layouts.app')

@section('title') Nationality @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Nationality',
    'subtitle' => 'Maintain nationality values for student registration forms.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Nationality'],
    ],
    'icon' => 'solar:flag-linear',
    'routePrefix' => 'admin.nationality',
    'searchPlaceholder' => 'Search nationality…',
    'actions' => [[
        'label' => 'Add Nationality',
        'url' => route('admin.nationality.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
