@extends('admin.layouts.app')
@section('title', 'Projects')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Projects','showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Projects']],'actions'=>auth('admin')->user()?->can('create projects')?[['label'=>'Add Project','url'=>route('admin.crm.projects.create'),'icon'=>'solar:add-circle-linear','class'=>'btn-primary-600 radius-8 px-20 py-11']]:[]])
    <div class="row g-3 mb-24">
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'Total','value'=>$stats['total'],'icon'=>'solar:folder-linear','tone'=>'navy'])</div>
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'In Progress','value'=>$stats['in_progress'],'icon'=>'solar:play-linear','tone'=>'amber'])</div>
        <div class="col-md-4">@include('admin.partials.dashboard-stat-card', ['label'=>'Completed','value'=>$stats['completed'],'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
    </div>
    @include('admin.partials.filter-bar', ['action'=>route('admin.crm.projects.index'),'resetUrl'=>route('admin.crm.projects.index'),'fields'=>[
        ['name'=>'search','label'=>'Search','placeholder'=>'Project name'],
        ['name'=>'status','label'=>'Status','type'=>'select','options'=>['pending'=>'Pending','in_progress'=>'In Progress','on_hold'=>'On Hold','completed'=>'Completed','cancelled'=>'Cancelled']],
        ['name'=>'customer_id','label'=>'Customer','type'=>'select','options'=>$customers->pluck('name','id')->all()],
    ]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Project</th><th>Customer</th><th>Status</th><th>Progress</th><th>Budget</th><th></th></tr></thead>
        <tbody>@forelse($projects as $project)<tr>
            <td><a href="{{ route('admin.crm.projects.show',$project) }}" class="fw-semibold">{{ $project->name }}</a><br><span class="text-sm text-secondary-light">{{ $project->project_code }}</span></td>
            <td>{{ $project->customer?->name }}</td><td>@include('admin.crm.partials.status-pill',['status'=>$project->status])</td>
            <td><div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ $project->progress }}%"></div></div><span class="text-sm">{{ $project->progress }}%</span></td>
            <td>{{ $project->budget ? number_format($project->budget,2) : '—' }}</td>
            <td>@include('admin.partials.table-actions',['viewUrl'=>route('admin.crm.projects.show',$project),'editUrl'=>auth('admin')->user()?->can('update projects')?route('admin.crm.projects.edit',$project):null,'deleteId'=>$project->id,'deleteRoute'=>route('admin.crm.projects.destroy',$project),'canDelete'=>auth('admin')->user()?->can('delete projects')])</td>
        </tr>@empty<tr><td colspan="6" class="text-center py-40 text-secondary-light">No projects found.</td></tr>@endforelse</tbody>
    </table></div></div></div>
    <div class="mt-24">{{ $projects->links() }}</div>
</div>
@endsection
