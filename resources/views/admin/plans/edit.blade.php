@extends('admin.layouts.app')

@section('title') Edit Plans @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Plans',
    'subtitle' => 'Update this plans entry.',
    'breadcrumbs' => [
        ['label' => 'Billing'],
        ['label' => 'Plans', 'url' => route('admin.plans.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.plans.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.plans.index'),
    'item' => $data,
    'submitLabel' => 'Update Plans',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
