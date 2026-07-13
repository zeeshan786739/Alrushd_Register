@extends('admin.layouts.app')

@section('title') Add Relationship @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Relationship',
    'subtitle' => 'Create a new relationship entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Relationship', 'url' => route('admin.relation-ships.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.relation-ships.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.relation-ships.index'),
    'submitLabel' => 'Save Relationship',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
