@extends('admin.layouts.app')

@section('title') Add Courses @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Courses',
    'subtitle' => 'Create a new courses entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Courses', 'url' => route('admin.student-course.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.student-course.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.student-course.index'),
    'submitLabel' => 'Save Courses',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
