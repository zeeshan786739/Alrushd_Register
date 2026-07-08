@extends('admin.layouts.app')

@section('title') Add Subjects @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Subjects',
    'subtitle' => 'Create a new subjects entry.',
    'breadcrumbs' => [
        ['label' => 'Academics'],
        ['label' => 'Subjects', 'url' => route('admin.subjects.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.subjects.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.subjects.index'),
    'submitLabel' => 'Save Subjects',
])
@endsection
