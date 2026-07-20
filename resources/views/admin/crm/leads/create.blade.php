@extends('admin.layouts.app')
@section('title', 'Create Lead')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Create Lead',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => 'Create'],
        ],
    ])
    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            <form method="POST" action="{{ route('admin.crm.leads.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-2"><label class="form-label">Title</label><input type="text" name="title" class="form-control radius-8" value="{{ old('title') }}"></div>
                    <div class="col-md-5"><label class="form-label">First Name *</label><input type="text" name="first_name" class="form-control radius-8" value="{{ old('first_name') }}" required></div>
                    <div class="col-md-5"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control radius-8" value="{{ old('last_name') }}"></div>
                    <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control radius-8" value="{{ old('email') }}"></div>
                    <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control radius-8" value="{{ old('phone') }}"></div>
                    <div class="col-md-4"><label class="form-label">Company</label><input type="text" name="company" class="form-control radius-8" value="{{ old('company') }}"></div>
                    <div class="col-md-4"><label class="form-label">Source</label><input type="text" name="lead_source" class="form-control radius-8" value="{{ old('lead_source') }}"></div>
                    <div class="col-md-4"><label class="form-label">Status</label><select name="lead_status" class="form-select radius-8">@foreach(\App\Enums\LeadStatus::cases() as $status)<option value="{{ $status->value }}" @selected(old('lead_status','new')==$status->value)>{{ str_replace('_',' ',$status->value) }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select radius-8">@foreach(\App\Enums\LeadPriority::cases() as $priority)<option value="{{ $priority->value }}" @selected(old('priority','medium')==$priority->value)>{{ ucfirst($priority->value) }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select radius-8"><option value="">Unassigned</option>@foreach($admins as $admin)<option value="{{ $admin->id }}" @selected(old('assigned_to')==$admin->id)>{{ $admin->name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Estimated Value</label><input type="number" step="0.01" name="estimated_value" class="form-control radius-8" value="{{ old('estimated_value') }}"></div>
                    <div class="col-md-4"><label class="form-label">Probability (%)</label><input type="number" min="0" max="100" name="probability" class="form-control radius-8" value="{{ old('probability', 0) }}"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="lead_description" class="form-control radius-8" rows="4">{{ old('lead_description') }}</textarea></div>
                </div>
                <div class="d-flex gap-12 mt-24">
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11">Create Lead</button>
                    <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-outline-neutral-500 radius-8 px-24 py-11">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
