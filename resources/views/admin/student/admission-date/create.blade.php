@extends('admin.layouts.app')

@section('title') Add Admission Date @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Admission Date',
    'subtitle' => 'Create a new admission date entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Admission Date', 'url' => route('admin.admission-date.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.admission-date.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.admission-date.index'),
    'submitLabel' => 'Save Admission Date',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
