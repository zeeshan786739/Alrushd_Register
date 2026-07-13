@extends('admin.layouts.app')

@section('title') Edit Gender @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Gender',
    'subtitle' => 'Update this gender entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Gender', 'url' => route('admin.genders.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.genders.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.genders.index'),
    'item' => $data,
    'submitLabel' => 'Update Gender',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
