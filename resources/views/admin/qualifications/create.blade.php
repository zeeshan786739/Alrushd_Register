@extends('admin.layouts.app')

@section('title') Add Qualifications @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Qualifications',
    'subtitle' => 'Create a new qualifications entry.',
    'breadcrumbs' => [
        ['label' => 'HR'],
        ['label' => 'Qualifications', 'url' => route('admin.qualifications.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.qualifications.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.qualifications.index'),
    'submitLabel' => 'Save Qualifications',
])
@endsection
