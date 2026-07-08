@extends('admin.layouts.app')

@section('title') Edit Languages @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Languages',
    'subtitle' => 'Update this languages entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Languages', 'url' => route('admin.student-language.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.student-language.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.student-language.index'),
    'item' => $data,
    'submitLabel' => 'Update Languages',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
