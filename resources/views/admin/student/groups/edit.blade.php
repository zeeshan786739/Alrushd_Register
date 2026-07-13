@extends('admin.layouts.app')

@section('title') Edit Student Groups @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Student Groups',
    'subtitle' => 'Update this student groups entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Student Groups', 'url' => route('admin.student-groups.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.student-groups.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.student-groups.index'),
    'item' => $data,
    'submitLabel' => 'Update Student Groups',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
