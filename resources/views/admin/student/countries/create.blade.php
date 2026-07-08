@extends('admin.layouts.app')

@section('title') Add Countries @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Countries',
    'subtitle' => 'Create a new countries entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Countries', 'url' => route('admin.countries.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.countries.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.countries.index'),
    'submitLabel' => 'Save Countries',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
