@extends('admin.layouts.app')

@section('title') Edit Languages @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Languages',
    'subtitle' => 'Update this languages entry.',
    'breadcrumbs' => [
        ['label' => 'Settings'],
        ['label' => 'Languages', 'url' => route('admin.languages.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.languages.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.languages.index'),
    'item' => $data,
    'submitLabel' => 'Update Languages',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
