@extends('admin.layouts.app')

@section('title') Edit Relationship @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Relationship',
    'subtitle' => 'Update this relationship entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Relationship', 'url' => route('admin.relation-ships.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.relation-ships.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.relation-ships.index'),
    'item' => $data,
    'submitLabel' => 'Update Relationship',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
