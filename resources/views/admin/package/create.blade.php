@extends('admin.layouts.app')

@section('title') Add Packages @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Packages',
    'subtitle' => 'Create a new packages entry.',
    'breadcrumbs' => [
        ['label' => 'Billing'],
        ['label' => 'Packages', 'url' => route('admin.package.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.package.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.package.index'),
    'submitLabel' => 'Save Packages',
])
@endsection
