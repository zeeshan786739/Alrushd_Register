@extends('admin.layouts.app')

@section('title') Add Student Groups @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Student Groups',
    'subtitle' => 'Create a new student groups entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Student Groups', 'url' => route('admin.student-groups.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.student-groups.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.student-groups.index'),
    'submitLabel' => 'Save Student Groups',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
