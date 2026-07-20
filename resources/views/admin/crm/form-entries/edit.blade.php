@extends('admin.layouts.app')
@section('title', 'Edit Submission')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', ['title'=>'Edit Submission','showBreadcrumb'=>true,'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Form Submissions','url'=>route('admin.crm.form-entries.index')],['label'=>'#'.$formEntry->id,'url'=>route('admin.crm.form-entries.show',$formEntry)],['label'=>'Edit']]])
    <div class="card radius-12 shadow-2 border-0"><div class="card-body p-24">
        <form method="POST" action="{{ route('admin.crm.form-entries.update',$formEntry) }}">@csrf @method('PUT')
            <div class="mb-20"><label class="form-label">Status</label><select name="status" class="form-select radius-8">@foreach(['pending','approved','rejected'] as $s)<option value="{{ $s }}" @selected(old('status',$formEntry->status)==$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
            @php $data = is_array($formEntry->data) ? $formEntry->data : []; @endphp
            <div class="row g-3">@foreach($data as $key=>$value)
                <div class="col-md-6"><label class="form-label">{{ str_replace('_',' ',ucfirst($key)) }}</label><input type="text" name="data[{{ $key }}]" class="form-control radius-8" value="{{ old('data.'.$key, is_array($value)?json_encode($value):$value) }}"></div>
            @endforeach</div>
            <div class="d-flex gap-12 mt-24"><button class="btn btn-primary-600 radius-8 px-24 py-11">Save Changes</button><a href="{{ route('admin.crm.form-entries.show',$formEntry) }}" class="btn btn-outline-neutral-500 radius-8 px-24 py-11">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection
