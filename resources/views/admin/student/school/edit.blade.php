@extends('admin.layouts.app')

@section('title') Edit Schools @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Schools',
    'subtitle' => 'Update this schools entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Schools', 'url' => route('admin.student-school.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.student-school.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.student-school.index'),
    'item' => $data,
    'submitLabel' => 'Update Schools',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
