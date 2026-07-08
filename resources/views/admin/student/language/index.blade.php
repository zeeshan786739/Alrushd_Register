@extends('admin.layouts.app')

@section('title') Languages @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Languages',
    'subtitle' => 'Manage language options for multilingual student data.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Languages'],
    ],
    'icon' => 'solar:translation-linear',
    'routePrefix' => 'admin.student-language',
    'searchPlaceholder' => 'Search languages…',
    'actions' => [[
        'label' => 'Add Languages',
        'url' => route('admin.student-language.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
