@extends('admin.layouts.app')

@section('title') Add Plans @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Plans',
    'subtitle' => 'Create a new plans entry.',
    'breadcrumbs' => [
        ['label' => 'Billing'],
        ['label' => 'Plans', 'url' => route('admin.plans.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.plans.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.plans.index'),
    'submitLabel' => 'Save Plans',
])
@endsection
