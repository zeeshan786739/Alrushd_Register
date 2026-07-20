@extends('admin.layouts.app')
@section('title', 'Form Submissions')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title'=>'Form Submissions','subtitle'=>'CRM view of form entries','showBreadcrumb'=>true,
        'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Form Submissions']],
        'actions'=>array_filter([
            auth('admin')->user()?->can('export form submissions')?['label'=>'Export','url'=>route('admin.crm.form-entries.export',request()->query()),'icon'=>'solar:export-linear','class'=>'btn-outline-neutral-500 radius-8 px-20 py-11']:null,
        ]),
    ])
    <div class="row g-3 mb-24">
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Total','value'=>$stats['total'],'icon'=>'solar:documents-linear','tone'=>'navy'])</div>
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Pending','value'=>$stats['pending'],'icon'=>'solar:clock-circle-linear','tone'=>'amber'])</div>
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Approved','value'=>$stats['approved'],'icon'=>'solar:check-circle-linear','tone'=>'green'])</div>
        <div class="col-md-3">@include('admin.partials.dashboard-stat-card', ['label'=>'Rejected','value'=>$stats['rejected'],'icon'=>'solar:close-circle-linear','tone'=>'rose'])</div>
    </div>
    @include('admin.partials.filter-bar', ['action'=>route('admin.crm.form-entries.index'),'resetUrl'=>route('admin.crm.form-entries.index'),'fields'=>[
        ['name'=>'search','label'=>'Search','placeholder'=>'Entry data'],
        ['name'=>'form_id','label'=>'Form','type'=>'select','options'=>$forms->pluck('name','id')->all()],
        ['name'=>'status','label'=>'Status','type'=>'select','options'=>['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected']],
        ['name'=>'date_from','label'=>'From','type'=>'date'],
        ['name'=>'date_to','label'=>'To','type'=>'date'],
    ]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>ID</th><th>Form</th><th>Status</th><th>Submitted</th><th>Preview</th><th></th></tr></thead>
        <tbody>@forelse($entries as $entry)@php $data = is_array($entry->data)?$entry->data:[]; $preview = collect($data)->take(2)->map(fn($v,$k)=>$k.': '.$v)->implode(' · '); @endphp
            <tr><td>#{{ $entry->id }}</td><td>{{ $entry->form?->name ?? '—' }}</td><td>@include('admin.crm.partials.status-pill',['status'=>$entry->status])</td><td>{{ optional($entry->submitted_at)->format('M j, Y H:i') ?? '—' }}</td><td class="text-sm">{{ Str::limit($preview, 80) }}</td>
            <td>@include('admin.partials.table-actions',['viewUrl'=>route('admin.crm.form-entries.show',$entry),'editUrl'=>auth('admin')->user()?->can('update form submissions')?route('admin.crm.form-entries.edit',$entry):null,'deleteId'=>$entry->id,'deleteRoute'=>route('admin.crm.form-entries.destroy',$entry),'canDelete'=>auth('admin')->user()?->can('delete form submissions')])</td></tr>
        @empty<tr><td colspan="6" class="text-center py-40 text-secondary-light">No submissions found.</td></tr>@endforelse</tbody>
    </table></div></div></div>
    <div class="mt-24">{{ $entries->links() }}</div>
</div>
@endsection
