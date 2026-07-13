@extends('admin.layouts.app')

@section('title') Add Coupons @endsection

@section('content')
@include('admin.partials.simple-crud-form', [
    'title' => 'Add Coupons',
    'subtitle' => 'Create a new coupons entry.',
    'breadcrumbs' => [
        ['label' => 'Billing'],
        ['label' => 'Coupons', 'url' => route('admin.coupons.index')],
        ['label' => 'Add'],
    ],
    'formAction' => route('admin.coupons.store'),
    'formMethod' => 'POST',
    'indexRoute' => route('admin.coupons.index'),
    'submitLabel' => 'Save Coupons',
])
@endsection
