@extends('admin.layouts.app')

@section('title') Edit Countries @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Countries',
    'subtitle' => 'Update this countries entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Countries', 'url' => route('admin.countries.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.countries.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.countries.index'),
    'item' => $data,
    'submitLabel' => 'Update Countries',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
