@extends('admin.layouts.app')

@section('title') Edit Academic Year @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Academic Year',
    'subtitle' => 'Update this academic year entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Academic Year', 'url' => route('admin.student-years.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.student-years.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.student-years.index'),
    'item' => $data,
    'submitLabel' => 'Update Academic Year',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
