@extends('admin.layouts.app')

@section('title') Terms & Conditions @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Terms & Conditions',
    'subtitle' => 'Manage terms and conditions shown during registration.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Terms & Conditions'],
    ],
    'icon' => 'solar:document-text-linear',
    'routePrefix' => 'admin.terms',
    'searchPlaceholder' => 'Search terms & conditions…',
    'actions' => [[
        'label' => 'Add Terms & Conditions',
        'url' => route('admin.terms.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
