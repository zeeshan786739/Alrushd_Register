@extends('admin.layouts.app')

@section('title') Gender @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Gender',
    'subtitle' => 'Manage gender options used across student profiles and admissions.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Gender'],
    ],
    'icon' => 'solar:user-rounded-linear',
    'routePrefix' => 'admin.genders',
    'searchPlaceholder' => 'Search gender…',
    'actions' => [[
        'label' => 'Add Gender',
        'url' => route('admin.genders.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
