@extends('admin.layouts.app')

@section('title') Add Languages @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Languages',
    'subtitle' => 'Create a new languages entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Languages', 'url' => route('admin.student-language.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.student-language.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.student-language.index'),
    'submitLabel' => 'Save Languages',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
