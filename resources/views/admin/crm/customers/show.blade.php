@extends('admin.layouts.app')
@section('title', $customer->name)
@section('content')
@include('admin.crm.partials.styles')
<div class="dashboard-main-body">
    @include('admin.partials.page-header', [
        'title'=>$customer->name,'subtitle'=>$customer->email,'showBreadcrumb'=>true,
        'breadcrumbs'=>[['label'=>'CRM'],['label'=>'Customers','url'=>route('admin.crm.customers.index')],['label'=>$customer->name]],
        'actions'=>array_filter([auth('admin')->user()?->can('update customers')?['label'=>'Edit','url'=>route('admin.crm.customers.edit',$customer),'icon'=>'solar:pen-linear','class'=>'btn-outline-primary-600 radius-8 px-20 py-11']:null]),
    ])
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card radius-12 shadow-2 border-0 mb-24"><div class="card-body p-24">
                @include('admin.crm.partials.status-pill', ['status'=>$customer->status])
                <div class="row g-3 mt-12">
                    <div class="col-md-6"><strong>Phone</strong><br>{{ $customer->phone ?? '—' }}</div>
                    <div class="col-md-6"><strong>Company</strong><br>{{ $customer->company ?? '—' }}</div>
                    <div class="col-md-6"><strong>Assigned</strong><br>{{ $customer->assignedAdmin?->name ?? '—' }}</div>
                    <div class="col-md-6"><strong>Lifetime Value</strong><br>{{ number_format($customer->lifetime_value,2) }}</div>
                    <div class="col-12"><strong>Notes</strong><br>{{ $customer->notes ?? '—' }}</div>
                </div>
            </div></div>
            <div class="card radius-12 shadow-2 border-0 mb-24"><div class="card-body p-24">
                <h6 class="fw-semibold mb-16">Contacts</h6>
                @forelse($customer->contacts as $contact)
                    <div class="d-flex justify-content-between border-bottom pb-12 mb-12"><div><strong>{{ $contact->name }}</strong>@if($contact->is_primary) <span class="badge bg-primary-50 text-primary-600">Primary</span>@endif<br>{{ $contact->email }} · {{ $contact->phone }}</div>
                    @can('update customers')<form method="POST" action="{{ route('admin.crm.customers.contacts.destroy', [$customer,$contact->id]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remove</button></form>@endcan</div>
                @empty<p class="text-secondary-light">No contacts added.</p>@endforelse
                @can('update customers')<form method="POST" action="{{ route('admin.crm.customers.contacts.store',$customer) }}" class="row g-2 mt-16">@csrf
                    <div class="col-md-3"><input name="name" class="form-control radius-8" placeholder="Name" required></div>
                    <div class="col-md-3"><input name="email" class="form-control radius-8" placeholder="Email"></div>
                    <div class="col-md-3"><input name="phone" class="form-control radius-8" placeholder="Phone"></div>
                    <div class="col-md-3"><button class="btn btn-primary-600 radius-8 w-100">Add Contact</button></div>
                </form>@endcan
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card radius-12 shadow-2 border-0 mb-24"><div class="card-body p-24">
                <h6 class="fw-semibold mb-16">Projects ({{ $customer->projects->count() }})</h6>
                @foreach($customer->projects->take(5) as $project)<a href="{{ route('admin.crm.projects.show',$project) }}" class="d-block mb-8">{{ $project->name }}</a>@endforeach
            </div></div>
            <div class="card radius-12 shadow-2 border-0"><div class="card-body p-24">
                <h6 class="fw-semibold mb-16">Activity</h6>
                @forelse($customer->activities->take(8) as $activity)
                    <div class="mb-12 pb-12 border-bottom"><div class="fw-semibold text-sm">{{ $activity->subject ?? ucfirst($activity->type) }}</div><div class="text-sm">{{ $activity->description }}</div></div>
                @empty<p class="text-secondary-light">No activity yet.</p>@endforelse
                @can('update customers')<form method="POST" action="{{ route('admin.crm.customers.activities.store',$customer) }}" class="mt-16">@csrf
                    <select name="type" class="form-select radius-8 mb-8"><option value="note">Note</option><option value="call">Call</option><option value="email">Email</option><option value="meeting">Meeting</option></select>
                    <input name="subject" class="form-control radius-8 mb-8" placeholder="Subject">
                    <textarea name="description" class="form-control radius-8 mb-8" rows="2" placeholder="Details"></textarea>
                    <button class="btn btn-primary-600 btn-sm radius-8 w-100">Log Activity</button>
                </form>@endcan
            </div></div>
        </div>
    </div>
</div>
@endsection
