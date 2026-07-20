@extends('admin.layouts.app')
@section('title', 'Create Customer')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Create Customer','showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Customers','url'=>route('admin.crm.customers.index')],['label'=>'Create']]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-24">
        <form method="POST" action="{{ route('admin.crm.customers.store') }}">@csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" class="form-control radius-8" value="{{ old('name') }}" required></div>
                <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control radius-8" value="{{ old('email') }}" required></div>
                <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control radius-8" value="{{ old('phone') }}"></div>
                <div class="col-md-4"><label class="form-label">Company</label><input type="text" name="company" class="form-control radius-8" value="{{ old('company') }}"></div>
                <div class="col-md-4"><label class="form-label">Website</label><input type="url" name="website" class="form-control radius-8" value="{{ old('website') }}"></div>
                <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select radius-8"><option value="active">Active</option><option value="inactive">Inactive</option><option value="prospect">Prospect</option></select></div>
                <div class="col-md-4"><label class="form-label">Source</label><input type="text" name="source" class="form-control radius-8" value="{{ old('source') }}"></div>
                <div class="col-md-4"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select radius-8"><option value="">Unassigned</option>@foreach($admins as $admin)<option value="{{ $admin->id }}">{{ $admin->name }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control radius-8" rows="2">{{ old('address') }}</textarea></div>
                <div class="col-md-4"><label class="form-label">City</label><input type="text" name="city" class="form-control radius-8" value="{{ old('city') }}"></div>
                <div class="col-md-4"><label class="form-label">State</label><input type="text" name="state" class="form-control radius-8" value="{{ old('state') }}"></div>
                <div class="col-md-4"><label class="form-label">Country</label><input type="text" name="country" class="form-control radius-8" value="{{ old('country') }}"></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control radius-8" rows="3">{{ old('notes') }}</textarea></div>
            </div>
            <div class="d-flex gap-12 mt-24"><button class="btn btn-primary-600 radius-8 px-24 py-11">Create Customer</button><a href="{{ route('admin.crm.customers.index') }}" class="btn btn-outline-neutral-500 radius-8 px-24 py-11">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection
