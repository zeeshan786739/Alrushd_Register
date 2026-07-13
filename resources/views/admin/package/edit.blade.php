@extends('admin.layouts.app')

@section('title') Edit Packages @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Packages',
    'subtitle' => 'Update this packages entry.',
    'breadcrumbs' => [
        ['label' => 'Billing'],
        ['label' => 'Packages', 'url' => route('admin.package.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.package.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.package.index'),
    'item' => $data,
    'submitLabel' => 'Update Packages',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
