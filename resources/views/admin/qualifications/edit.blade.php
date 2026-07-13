@extends('admin.layouts.app')

@section('title') Edit Qualifications @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Qualifications',
    'subtitle' => 'Update this qualifications entry.',
    'breadcrumbs' => [
        ['label' => 'HR'],
        ['label' => 'Qualifications', 'url' => route('admin.qualifications.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.qualifications.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.qualifications.index'),
    'item' => $data,
    'submitLabel' => 'Update Qualifications',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
