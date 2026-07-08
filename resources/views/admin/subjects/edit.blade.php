@extends('admin.layouts.app')

@section('title') Edit Subjects @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Subjects',
    'subtitle' => 'Update this subjects entry.',
    'breadcrumbs' => [
        ['label' => 'Academics'],
        ['label' => 'Subjects', 'url' => route('admin.subjects.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.subjects.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.subjects.index'),
    'item' => $data,
    'submitLabel' => 'Update Subjects',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
