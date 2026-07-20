@extends('admin.layouts.app')
@section('title', $lead->full_name)
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title' => $lead->full_name,
        'subtitle' => $lead->email ?? $lead->phone ?? 'Lead details',
        'showBreadcrumb' => true,
        'breadcrumbs' => [
            ['label' => 'CRM'],
            ['label' => 'Leads', 'url' => route('admin.crm.leads.index')],
            ['label' => $lead->full_name],
        ],
        'actions' => array_filter([
            auth('admin')->user()?->can('update leads') ? ['label'=>'Edit','url'=>route('admin.crm.leads.edit',$lead),'icon'=>'solar:pen-linear','class'=>'btn-outline-primary-600 radius-8 px-20 py-11'] : null,
            auth('admin')->user()?->can('update leads') && $lead->email ? ['label'=>'Email Lead','url'=>route('admin.crm.leads.email.form',$lead),'icon'=>'solar:letter-linear','class'=>'btn-outline-primary-600 radius-8 px-20 py-11'] : null,
            auth('admin')->user()?->can('compose emails') && $lead->email ? ['label'=>'Compose (EM)','url'=>route('admin.email.compose',['to'=>$lead->email,'lead_id'=>$lead->id,'subject'=>'Follow up']),'icon'=>'solar:pen-new-square-linear','class'=>'btn-outline-primary-600 radius-8 px-20 py-11'] : null,
        ]),
    ])

    <div class="row g-3 mb-24">
        <div class="col-md-8">
            <div class="card radius-12 shadow-2 border-0 mb-24">
                <div class="card-body p-24">
                    <div class="d-flex flex-wrap gap-12 mb-20">
                        @include('admin.crm.partials.status-pill', ['status'=>$lead->lead_status])
                        @include('admin.crm.partials.status-pill', ['status'=>$lead->priority])
                        @if($lead->is_converted)<span class="crm-status-pill crm-status-pill--accepted">Converted</span>@endif
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Email</strong><br>{{ $lead->email ?? '—' }}</div>
                        <div class="col-md-6"><strong>Phone</strong><br>{{ $lead->phone ?? '—' }}</div>
                        <div class="col-md-6"><strong>Company</strong><br>{{ $lead->company ?? '—' }}</div>
                        <div class="col-md-6"><strong>Source</strong><br>{{ $lead->lead_source ?? $lead->source ?? '—' }}</div>
                        <div class="col-md-6"><strong>Assigned To</strong><br>{{ $lead->assignedAdmin?->name ?? 'Unassigned' }}</div>
                        <div class="col-md-6"><strong>Estimated Value</strong><br>{{ $lead->estimated_value ? number_format($lead->estimated_value,2) : '—' }}</div>
                        <div class="col-12"><strong>Description</strong><br>{{ $lead->lead_description ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="card radius-12 shadow-2 border-0 mb-24">
                <div class="card-body p-24">
                    <h6 class="fw-semibold mb-16">Notes</h6>
                    @forelse($lead->notes as $note)
                        <div class="border-bottom pb-12 mb-12">
                            <div class="d-flex justify-content-between"><strong>{{ $note->admin?->name }}</strong><span class="text-sm text-secondary-light">{{ $note->created_at->diffForHumans() }}</span></div>
                            <p class="mb-0 mt-4">{{ $note->note }}</p>
                        </div>
                    @empty
                        <p class="text-secondary-light mb-0">No notes yet.</p>
                    @endforelse
                    @can('update leads')
                    <form method="POST" action="{{ route('admin.crm.leads.notes.store', $lead) }}" class="mt-20">
                        @csrf
                        <textarea name="note" class="form-control radius-8 mb-12" rows="3" placeholder="Add a note..." required></textarea>
                        <button type="submit" class="btn btn-primary-600 radius-8 btn-sm">Add Note</button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @can('update leads')
            <div class="card radius-12 shadow-2 border-0 mb-24">
                <div class="card-body p-24">
                    <h6 class="fw-semibold mb-16">Quick Actions</h6>
                    <form method="POST" action="{{ route('admin.crm.leads.status.update', $lead) }}" class="mb-16">@csrf @method('PATCH')
                        <label class="form-label text-sm">Update Status</label>
                        <select name="lead_status" class="form-select radius-8 mb-8">@foreach(\App\Enums\LeadStatus::cases() as $status)<option value="{{ $status->value }}" @selected($lead->lead_status==$status->value)>{{ str_replace('_',' ',$status->value) }}</option>@endforeach</select>
                        <button class="btn btn-outline-primary-600 btn-sm radius-8 w-100">Update Status</button>
                    </form>
                    <form method="POST" action="{{ route('admin.crm.leads.follow-up', $lead) }}" class="mb-16">@csrf @method('PATCH')
                        <label class="form-label text-sm" for="next_follow_up_date">Follow-up Date</label>
                        <input type="date" id="next_follow_up_date" name="next_follow_up_date" class="form-control radius-8 mb-8" value="{{ old('next_follow_up_date', optional($lead->next_follow_up_date)->format('Y-m-d')) }}" required>
                        <label class="form-label text-sm" for="next_follow_up_time">Time</label>
                        <input type="time" id="next_follow_up_time" name="next_follow_up_time" class="form-control radius-8 mb-8" value="{{ old('next_follow_up_time', $lead->next_follow_up_time) }}">
                        <label class="form-label text-sm" for="next_follow_up_type">Type</label>
                        <input type="text" id="next_follow_up_type" name="next_follow_up_type" class="form-control radius-8 mb-8" value="{{ old('next_follow_up_type', $lead->next_follow_up_type) }}" placeholder="Call, email, visit">
                        <button class="btn btn-outline-primary-600 btn-sm radius-8 w-100">Schedule Follow-up</button>
                    </form>
                    <form method="POST" action="{{ route('admin.crm.leads.appointment', $lead) }}" class="mb-16">@csrf @method('PATCH')
                        <label class="form-label text-sm" for="appointment_date">Appointment</label>
                        <input type="datetime-local" id="appointment_date" name="appointment_date" class="form-control radius-8 mb-8" value="{{ old('appointment_date', optional($lead->appointment_date)->format('Y-m-d\TH:i')) }}" required>
                        <label class="form-label text-sm" for="appointment_type">Appointment Type</label>
                        <select id="appointment_type" name="appointment_type" class="form-select radius-8 mb-8" required>
                            <option value="school_visit" @selected(old('appointment_type', $lead->appointment_type)==='school_visit')>School Visit</option>
                            <option value="online_meeting" @selected(old('appointment_type', $lead->appointment_type)==='online_meeting')>Online Meeting</option>
                            <option value="phone_call" @selected(old('appointment_type', $lead->appointment_type)==='phone_call')>Phone Call</option>
                        </select>
                        <label class="form-label text-sm" for="appointment_notes">Notes</label>
                        <textarea id="appointment_notes" name="appointment_notes" class="form-control radius-8 mb-8" rows="2">{{ old('appointment_notes', $lead->appointment_notes) }}</textarea>
                        <button class="btn btn-outline-primary-600 btn-sm radius-8 w-100">Schedule Appointment</button>
                    </form>
                    @can('assign leads')
                    <form method="POST" action="{{ route('admin.crm.leads.assign', $lead) }}" class="mb-16">@csrf @method('PATCH')
                        <label class="form-label text-sm">Assign To</label>
                        <select name="assigned_to" class="form-select radius-8 mb-8">@foreach($admins as $admin)<option value="{{ $admin->id }}" @selected($lead->assigned_to==$admin->id)>{{ $admin->name }}</option>@endforeach</select>
                        <button class="btn btn-outline-primary-600 btn-sm radius-8 w-100">Assign</button>
                    </form>
                    @endcan
                    @can('convert leads')
                    @if(!$lead->is_converted)
                    <form method="POST" action="{{ route('admin.crm.leads.convert', $lead) }}" onsubmit="return confirm('Convert this lead to a customer?')">@csrf
                        <button class="btn btn-primary-600 btn-sm radius-8 w-100">Convert to Customer</button>
                    </form>
                    @elseif($lead->customer)
                    <a href="{{ route('admin.crm.customers.show', $lead->customer) }}" class="btn btn-outline-success btn-sm radius-8 w-100 mt-12">View Customer</a>
                    @endif
                    @endcan
                </div>
            </div>
            @endcan
            <div class="card radius-12 shadow-2 border-0">
                <div class="card-body p-24">
                    <h6 class="fw-semibold mb-16">Activity</h6>
                    @forelse($lead->activities as $activity)
                        <div class="mb-12 pb-12 border-bottom">
                            <div class="text-sm fw-semibold">{{ str_replace('_',' ',$activity->activity_type) }}</div>
                            <div class="text-sm">{{ $activity->description }}</div>
                            <div class="text-xs text-secondary-light">{{ $activity->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <p class="text-secondary-light mb-0">No activity logged.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
