@extends('admin.layouts.app')

@section('title') Add Subjects @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Subjects',
    'subtitle' => 'Create a new subjects entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Subjects', 'url' => route('admin.student-subject.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.student-subject.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.student-subject.index'),
    'submitLabel' => 'Save Subjects',
    'submitIcon' => 'solar:diskette-linear',
])
@endsection
