@extends('admin.layouts.app')

@section('title') Add Group Years @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Group Years',
    'subtitle' => 'Create a new group years entry.',
    'breadcrumbs' => [
        ['label' => 'Academics'],
        ['label' => 'Group Years', 'url' => route('admin.group-years.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.group-years.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.group-years.index'),
    'submitLabel' => 'Save Group Years',
])
@endsection
