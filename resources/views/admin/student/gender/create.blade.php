@extends('admin.layouts.app')

@section('title') Add Gender @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Gender',
    'subtitle' => 'Create a new gender entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Gender', 'url' => route('admin.genders.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.genders.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.genders.index'),
    'submitLabel' => 'Save Gender',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
