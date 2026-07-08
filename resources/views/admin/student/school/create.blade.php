@extends('admin.layouts.app')

@section('title') Add Schools @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Schools',
    'subtitle' => 'Create a new schools entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Schools', 'url' => route('admin.student-school.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.student-school.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.student-school.index'),
    'submitLabel' => 'Save Schools',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
