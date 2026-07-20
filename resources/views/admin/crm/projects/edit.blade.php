@extends('admin.layouts.app')
@section('title', 'Edit Project')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Edit Project','showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Projects','url'=>route('admin.crm.projects.index')],['label'=>$project->name,'url'=>route('admin.crm.projects.show',$project)],['label'=>'Edit']]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-24">
        <form method="POST" action="{{ route('admin.crm.projects.update',$project) }}">@csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Customer *</label><select name="customer_id" class="form-select radius-8" required>@foreach($customers as $c)<option value="{{ $c->id }}" @selected(old('customer_id',$project->customer_id)==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Project Name *</label><input type="text" name="name" class="form-control radius-8" value="{{ old('name',$project->name) }}" required></div>
                <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select radius-8">@foreach(['pending','in_progress','on_hold','completed','cancelled'] as $s)<option value="{{ $s }}" @selected(old('status',$project->status)==$s)>{{ str_replace('_',' ',ucfirst($s)) }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select radius-8">@foreach(['low','medium','high','urgent'] as $p)<option value="{{ $p }}" @selected(old('priority',$project->priority)==$p)>{{ ucfirst($p) }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">Budget</label><input type="number" step="0.01" name="budget" class="form-control radius-8" value="{{ old('budget',$project->budget) }}"></div>
                <div class="col-md-4"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control radius-8" value="{{ old('start_date',optional($project->start_date)->format('Y-m-d')) }}"></div>
                <div class="col-md-4"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control radius-8" value="{{ old('end_date',optional($project->end_date)->format('Y-m-d')) }}"></div>
                <div class="col-md-4"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select radius-8"><option value="">Unassigned</option>@foreach($admins as $admin)<option value="{{ $admin->id }}" @selected(old('assigned_to',$project->assigned_to)==$admin->id)>{{ $admin->name }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control radius-8" rows="3">{{ old('description',$project->description) }}</textarea></div>
            </div>
            <div class="d-flex gap-12 mt-24"><button class="btn btn-primary-600 radius-8 px-24 py-11">Save Changes</button><a href="{{ route('admin.crm.projects.show',$project) }}" class="btn btn-outline-neutral-500 radius-8 px-24 py-11">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection
