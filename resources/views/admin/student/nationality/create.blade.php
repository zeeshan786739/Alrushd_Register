@extends('admin.layouts.app')

@section('title') Add Nationality @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Nationality',
    'subtitle' => 'Create a new nationality entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Nationality', 'url' => route('admin.nationality.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.nationality.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.nationality.index'),
    'submitLabel' => 'Save Nationality',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
