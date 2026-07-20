@extends('admin.layouts.app')
@section('title', 'Create Project')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Create Project','showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Projects','url'=>route('admin.crm.projects.index')],['label'=>'Create']]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-24">
        <form method="POST" action="{{ route('admin.crm.projects.store') }}">@csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Customer *</label><select name="customer_id" class="form-select radius-8" required>@foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id',$selectedCustomer)==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Project Name *</label><input type="text" name="name" class="form-control radius-8" value="{{ old('name') }}" required></div>
                <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select radius-8"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="on_hold">On Hold</option><option value="completed">Completed</option></select></div>
                <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select radius-8"><option value="medium">Medium</option><option value="low">Low</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
                <div class="col-md-4"><label class="form-label">Budget</label><input type="number" step="0.01" name="budget" class="form-control radius-8" value="{{ old('budget') }}"></div>
                <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control radius-8" value="{{ old('start_date') }}"></div>
                <div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control radius-8" value="{{ old('end_date') }}"></div>
                <div class="col-md-4"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select radius-8"><option value="">Unassigned</option>@foreach($admins as $admin)<option value="{{ $admin->id }}">{{ $admin->name }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control radius-8" rows="3">{{ old('description') }}</textarea></div>
            </div>
            <div class="d-flex gap-12 mt-24"><button class="btn btn-primary-600 radius-8 px-24 py-11">Create Project</button><a href="{{ route('admin.crm.projects.index') }}" class="btn btn-outline-neutral-500 radius-8 px-24 py-11">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection
