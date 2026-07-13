@extends('admin.layouts.app')

@section('title') Add Languages @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Languages',
    'subtitle' => 'Create a new languages entry.',
    'breadcrumbs' => [
        ['label' => 'Settings'],
        ['label' => 'Languages', 'url' => route('admin.languages.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.languages.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.languages.index'),
    'submitLabel' => 'Save Languages',
])
@endsection
