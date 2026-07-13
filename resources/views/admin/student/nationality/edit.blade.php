@extends('admin.layouts.app')

@section('title') Edit Nationality @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Nationality',
    'subtitle' => 'Update this nationality entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Nationality', 'url' => route('admin.nationality.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.nationality.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.nationality.index'),
    'item' => $data,
    'submitLabel' => 'Update Nationality',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
