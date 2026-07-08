@extends('admin.layouts.app')

@section('title') Edit Groups @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Groups',
    'subtitle' => 'Update this groups entry.',
    'breadcrumbs' => [
        ['label' => 'Academics'],
        ['label' => 'Groups', 'url' => route('admin.groups.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.groups.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.groups.index'),
    'item' => $data,
    'submitLabel' => 'Update Groups',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
