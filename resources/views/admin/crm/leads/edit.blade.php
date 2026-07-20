@extends('admin.layouts.app')
@section('title', 'Edit Lead')
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => 'Edit Lead',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => $lead->full_name, 'url' => route('admin.crm.leads.show', $lead)],
            ['label' => 'Edit'],
        ],
    ])
    <div class="card radius-12 shadow-2 border-0">
        <div class="card-body p-24">
            <form method="POST" action="{{ route('admin.crm.leads.update', $lead) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-2"><label class="form-label">Title</label><input type="text" name="title" class="form-control radius-8" value="{{ old('title', $lead->title) }}"></div>
                    <div class="col-md-5"><label class="form-label">First Name *</label><input type="text" name="first_name" class="form-control radius-8" value="{{ old('first_name', $lead->first_name) }}" required></div>
                    <div class="col-md-5"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control radius-8" value="{{ old('last_name', $lead->last_name) }}"></div>
                    <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control radius-8" value="{{ old('email', $lead->email) }}"></div>
                    <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control radius-8" value="{{ old('phone', $lead->phone) }}"></div>
                    <div class="col-md-4"><label class="form-label">Company</label><input type="text" name="company" class="form-control radius-8" value="{{ old('company', $lead->company) }}"></div>
                    <div class="col-md-4"><label class="form-label">Source</label><input type="text" name="lead_source" class="form-control radius-8" value="{{ old('lead_source', $lead->lead_source) }}"></div>
                    <div class="col-md-4"><label class="form-label">Status</label><select name="lead_status" class="form-select radius-8">@foreach(\App\Enums\LeadStatus::cases() as $status)<option value="{{ $status->value }}" @selected(old('lead_status',$lead->lead_status)==$status->value)>{{ str_replace('_',' ',$status->value) }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select radius-8">@foreach(\App\Enums\LeadPriority::cases() as $priority)<option value="{{ $priority->value }}" @selected(old('priority',$lead->priority)==$priority->value)>{{ ucfirst($priority->value) }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select radius-8"><option value="">Unassigned</option>@foreach($admins as $admin)<option value="{{ $admin->id }}" @selected(old('assigned_to',$lead->assigned_to)==$admin->id)>{{ $admin->name }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Estimated Value</label><input type="number" step="0.01" name="estimated_value" class="form-control radius-8" value="{{ old('estimated_value', $lead->estimated_value) }}"></div>
                    <div class="col-md-4"><label class="form-label">Probability (%)</label><input type="number" min="0" max="100" name="probability" class="form-control radius-8" value="{{ old('probability', $lead->probability) }}"></div>
                    <div class="col-md-4"><label class="form-label">Follow-up Date</label><input type="date" name="next_follow_up_date" class="form-control radius-8" value="{{ old('next_follow_up_date', optional($lead->next_follow_up_date)->format('Y-m-d')) }}"></div>
                    <div class="col-md-6"><label class="form-label">Address</label><input type="text" name="address" class="form-control radius-8" value="{{ old('address', $lead->address) }}"></div>
                    <div class="col-md-3"><label class="form-label">City</label><input type="text" name="city" class="form-control radius-8" value="{{ old('city', $lead->city) }}"></div>
                    <div class="col-md-3"><label class="form-label">Postal Code</label><input type="text" name="postal_code" class="form-control radius-8" value="{{ old('postal_code', $lead->postal_code) }}"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="lead_description" class="form-control radius-8" rows="4">{{ old('lead_description', $lead->lead_description) }}</textarea></div>
                </div>
                <div class="d-flex gap-12 mt-24">
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11">Save Changes</button>
                    <a href="{{ route('admin.crm.leads.show', $lead) }}" class="btn btn-outline-neutral-500 radius-8 px-24 py-11">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
