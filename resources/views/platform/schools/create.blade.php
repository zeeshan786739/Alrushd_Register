@extends('platform.layouts.app')

@section('title', 'New School')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-24">
    <div>
        <h6 class="fw-semibold mb-0">Create a School</h6>
        <span class="text-secondary-light text-sm">Provision a new organisation with its first admin account.</span>
    </div>
    <a href="{{ route('platform.schools.index') }}" class="btn btn-outline-secondary">Back to Schools</a>
</div>

@if($errors->any())
<div class="alert alert-danger radius-8">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('platform.schools.store') }}">
    @csrf
    @if($demoRequest)
        <input type="hidden" name="demo_request_id" value="{{ $demoRequest->id }}">
        <div class="alert alert-info radius-8 d-flex align-items-center gap-2">
            <iconify-icon icon="solar:info-circle-linear"></iconify-icon>
            Converting demo request from <strong>{{ $demoRequest->name }}</strong> ({{ $demoRequest->email }}).
        </div>
    @endif

    <div class="row gy-4">
        <div class="col-lg-7">
            <div class="card radius-12 border-0 shadow-sm mb-24">
                <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">School Details</h6></div>
                <div class="card-body p-24 row g-3">
                    <div class="col-md-6">
                        <label class="form-label">School / Organisation name *</label>
                        <input type="text" name="name" class="form-control" required
                               value="{{ old('name', $demoRequest?->organization_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $demoRequest?->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact person</label>
                        <input type="text" name="contact_name" class="form-control" value="{{ old('contact_name', $demoRequest?->name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $demoRequest?->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Website</label>
                        <input type="text" name="website" class="form-control" value="{{ old('website') }}" placeholder="https://">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ old('country', $demoRequest?->country) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Timezone</label>
                        <input type="text" name="timezone" class="form-control" value="{{ old('timezone', 'UTC') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Internal notes</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card radius-12 border-0 shadow-sm">
                <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">School Admin Account</h6></div>
                <div class="card-body p-24 row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Admin name *</label>
                        <input type="text" name="admin_name" class="form-control" required value="{{ old('admin_name', $demoRequest?->name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Admin email *</label>
                        <input type="email" name="admin_email" class="form-control" required value="{{ old('admin_email', $demoRequest?->email) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password *</label>
                        <input type="text" name="admin_password" class="form-control" required minlength="8"
                               placeholder="Min 8 characters" value="{{ old('admin_password') }}">
                    </div>
                    <div class="col-12">
                        <span class="text-secondary-light text-sm">The admin logs in at <code>/admin/login</code> with these credentials and full access to their school panel.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card radius-12 border-0 shadow-sm">
                <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Plan &amp; Status</h6></div>
                <div class="card-body p-24 row g-3">
                    <div class="col-12">
                        <label class="form-label">Subscription plan</label>
                        <select name="saas_plan_id" class="form-select">
                            <option value="">No plan</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('saas_plan_id') == $plan->id)>
                                    {{ $plan->name }} — {{ $plan->formattedPrice() }}/{{ $plan->billing_interval }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Subscription type</label>
                        <select name="subscription_type" class="form-select">
                            <option value="trial" @selected(old('subscription_type', 'trial') === 'trial')>Trial (plan's trial days)</option>
                            <option value="complimentary" @selected(old('subscription_type') === 'complimentary')>Complimentary (free forever)</option>
                            <option value="none" @selected(old('subscription_type') === 'none')>No subscription record</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Account status</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', 'trial') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 pt-2">
                        <button type="submit" class="btn btn-primary w-100 py-12">Create School</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
