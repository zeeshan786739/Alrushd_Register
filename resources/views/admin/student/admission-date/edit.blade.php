@extends('admin.layouts.app')

@section('title') Edit Admission Date @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Admission Date',
    'subtitle' => 'Update this admission date entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Admission Date', 'url' => route('admin.admission-date.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.admission-date.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.admission-date.index'),
    'item' => $data,
    'submitLabel' => 'Update Admission Date',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
