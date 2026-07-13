@extends('admin.layouts.app')

@section('title') Add Groups @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Groups',
    'subtitle' => 'Create a new groups entry.',
    'breadcrumbs' => [
        ['label' => 'Academics'],
        ['label' => 'Groups', 'url' => route('admin.groups.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.groups.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.groups.index'),
    'submitLabel' => 'Save Groups',
])
@endsection
