@extends('admin.layouts.app')

@section('title') Edit Coupons @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Edit Coupons',
    'subtitle' => 'Update this coupons entry.',
    'breadcrumbs' => [
        ['label' => 'Billing'],
        ['label' => 'Coupons', 'url' => route('admin.coupons.index')],
        ['label' => 'Edit'],
    ],
    'formAction' => route('admin.coupons.update', $data->id),
    'formMethod' => 'PUT',
    'indexRoute' => route('admin.coupons.index'),
    'item' => $data,
    'submitLabel' => 'Update Coupons',
    'submitIcon' => 'solar:pen-linear',
])
@endsection
