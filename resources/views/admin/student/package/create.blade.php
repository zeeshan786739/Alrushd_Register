@extends('admin.layouts.app')

@section('title') Add Packages @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Packages',
    'subtitle' => 'Create a new packages entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Packages', 'url' => route('admin.student-package.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.student-package.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.student-package.index'),
    'submitLabel' => 'Save Packages',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
