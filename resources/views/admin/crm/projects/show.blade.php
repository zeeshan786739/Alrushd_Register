@extends('admin.layouts.app')
@section('title', $project->name)
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>$project->name,'subtitle'=>$project->project_code,'showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Projects','url'=>route('admin.crm.projects.index')],['label'=>$project->name]],'actions'=>array_filter([auth('admin')->user()?->can('update projects')?['label'=>'Edit','url'=>route('admin.crm.projects.edit',$project),'icon'=>'solar:pen-linear','class'=>'btn-outline-primary-600 radius-8 px-20 py-11']:null])])
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card radius-12 shadow-2 border-0 mb-24"><div class="card-body p-24">
                <div class="d-flex gap-12 mb-16">@include('admin.crm.partials.status-pill',['status'=>$project->status])<span class="text-sm">Progress: {{ $project->progress }}%</span></div>
                <p>{{ $project->description ?? 'No description.' }}</p>
                <div class="row g-3"><div class="col-md-4"><strong>Customer</strong><br><a href="{{ route('admin.crm.customers.show',$project->customer) }}">{{ $project->customer?->name }}</a></div>
                <div class="col-md-4"><strong>Budget</strong><br>{{ $project->budget ? number_format($project->budget,2) : '—' }}</div>
                <div class="col-md-4"><strong>Assigned</strong><br>{{ $project->assignedAdmin?->name ?? '—' }}</div></div>
            </div></div>
            <div class="card radius-12 shadow-2 border-0"><div class="card-body p-24">
                <h6 class="fw-semibold mb-16">Tasks</h6>
                @forelse($project->tasks as $task)
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-12 mb-12">
                        <div><strong>{{ $task->name }}</strong><br><span class="text-sm">@include('admin.crm.partials.status-pill',['status'=>$task->status]) · Due {{ $task->due_date?->format('M j, Y') ?? '—' }}</span></div>
                        @can('update projects')<form method="POST" action="{{ route('admin.crm.projects.tasks.destroy',[$project,$task->id]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remove</button></form>@endcan
                    </div>
                @empty<p class="text-secondary-light">No tasks yet.</p>@endforelse
                @can('update projects')<form method="POST" action="{{ route('admin.crm.projects.tasks.store',$project) }}" class="row g-2 mt-16">@csrf
                    <div class="col-md-4"><input name="name" class="form-control radius-8" placeholder="Task name" required></div>
                    <div class="col-md-3"><select name="status" class="form-select radius-8"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select></div>
                    <div class="col-md-3"><input type="date" name="due_date" class="form-control radius-8"></div>
                    <div class="col-md-2"><button class="btn btn-primary-600 radius-8 w-100">Add</button></div>
                    <input type="hidden" name="priority" value="medium">
                </form>@endcan
            </div></div>
        </div>
    </div>
</div>
@endsection
