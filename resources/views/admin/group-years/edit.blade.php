@extends('admin.layouts.app')

@section('title') Edit Group Years @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Group Years',
    'subtitle' => 'Update this group years entry.',
    'breadcrumbs' => [
        ['label' => 'Academics'],
        ['label' => 'Group Years', 'url' => route('admin.group-years.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.group-years.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.group-years.index'),
    'item' => $data,
    'submitLabel' => 'Update Group Years',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
