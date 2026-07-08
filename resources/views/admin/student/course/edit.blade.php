@extends('admin.layouts.app')

@section('title') Edit Courses @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Courses',
    'subtitle' => 'Update this courses entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Courses', 'url' => route('admin.student-course.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.student-course.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.student-course.index'),
    'item' => $data,
    'submitLabel' => 'Update Courses',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
