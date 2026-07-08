@extends('admin.layouts.app')

@section('title') Languages @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Languages',
    'subtitle' => 'Manage language options used across the platform.',
    'breadcrumbs' => [
        ['label' => 'Settings'],
        ['label' => 'Languages'],
    ],
    'icon' => 'solar:translation-linear',
    'routePrefix' => 'admin.languages',
    'searchPlaceholder' => 'Search languages…',
    'actions' => [[
        'label' => 'Add Languages',
        'url' => route('admin.languages.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
