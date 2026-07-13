@extends('admin.layouts.app')

@section('title') Edit Subjects @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Subjects',
    'subtitle' => 'Update this subjects entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Subjects', 'url' => route('admin.student-subject.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.student-subject.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.student-subject.index'),
    'item' => $data,
    'submitLabel' => 'Update Subjects',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
