@extends('admin.layouts.app')

@section('title') Add Academic Year @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Academic Year',
    'subtitle' => 'Create a new academic year entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Academic Year', 'url' => route('admin.student-years.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.student-years.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.student-years.index'),
    'submitLabel' => 'Save Academic Year',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
