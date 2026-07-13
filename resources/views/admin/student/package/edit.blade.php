@extends('admin.layouts.app')

@section('title') Edit Packages @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Packages',
    'subtitle' => 'Update this packages entry.',
    'breadcrumbs' => [
        ['label' => 'Student Setup'],
        ['label' => 'Packages', 'url' => route('admin.student-package.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.student-package.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.student-package.index'),
    'item' => $data,
    'submitLabel' => 'Update Packages',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
