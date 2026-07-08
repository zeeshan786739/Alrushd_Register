@extends('admin.layouts.app')

@section('title') Relationship @endsection

@section('content')
@include('admin.partials.simple-crud-index', [
    'title' => 'Relationship',
    'subtitle' => 'Define guardian and emergency contact relationship types.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Relationship'],
    ],
    'icon' => 'solar:users-group-two-rounded-linear',
    'routePrefix' => 'admin.relation-ships',
    'searchPlaceholder' => 'Search relationship…',
    'actions' => [[
        'label' => 'Add Relationship',
        'url' => route('admin.relation-ships.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:add-circle-linear',
    ]],
    'data' => $data,
])
@endsection
