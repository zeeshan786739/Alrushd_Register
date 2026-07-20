@extends('admin.layouts.app')
@section('title', 'Submission #'.$formEntry->id)
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title'=>'Submission #'.$formEntry->id,'subtitle'=>$formEntry->form?->name,'showBreadcrumb'=>true,
        'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Form Submissions','url'=>route('admin.crm.form-entries.index')],['label'=>'#'.$formEntry->id]],
        'actions'=>array_filter([
            auth('admin')->user()?->can('update form submissions')?['label'=>'Edit','url'=>route('admin.crm.form-entries.edit',$formEntry),'icon'=>'solar:pen-linear','class'=>'btn-outline-primary-600 radius-8 px-20 py-11']:null,
        ]),
    ])
    <div class="card radius-12 shadow-2 border-0 mb-24"><div class="card-body p-24">
        <div class="d-flex gap-12 mb-20">@include('admin.crm.partials.status-pill',['status'=>$formEntry->status])<span class="text-sm text-secondary-light">Submitted {{ optional($formEntry->submitted_at)->format('M j, Y H:i') ?? '—' }}</span></div>
        <div class="row g-3">@php $data = is_array($formEntry->data) ? $formEntry->data : []; @endphp
            @forelse($data as $key => $value)
                <div class="col-md-6"><strong>{{ str_replace('_',' ',ucfirst($key)) }}</strong><br>{{ is_array($value) ? json_encode($value) : $value }}</div>
            @empty<p class="text-secondary-light">No submission data.</p>@endforelse
        </div>
    </div></div>
    @can('convert form submissions')
    <div class="d-flex flex-wrap gap-12">
        <form method="POST" action="{{ route('admin.crm.form-entries.convert-lead',$formEntry) }}">@csrf<button class="btn btn-primary-600 radius-8">Convert to Lead</button></form>
        <form method="POST" action="{{ route('admin.crm.form-entries.convert-customer',$formEntry) }}">@csrf<button class="btn btn-outline-primary-600 radius-8">Convert to Customer</button></form>
    </div>
    @endcan
</div>
@endsection
