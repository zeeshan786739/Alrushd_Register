@extends('platform.layouts.app')

@section('title', $organization->name)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div class="d-flex align-items-center gap-3">
        <div>
            <h6 class="fw-semibold mb-0">{{ $organization->name }}</h6>
            <span class="text-secondary-light text-sm">/{{ $organization->slug }} · joined {{ $organization->created_at->format('d M Y') }}</span>
        </div>
        <span class="badge platform-badge {{ $organization->status?->badgeClass() }}">{{ $organization->status?->label() }}</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('platform.schools.edit', $organization) }}" class="btn btn-outline-primary">Edit Details</a>
        <a href="{{ route('platform.schools.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="row gy-4">
    <div class="col-xxl-8">

        {{-- Usage stats --}}
        <div class="row gy-3 mb-24">
            @foreach($usage as $label => $count)
            <div class="col-md-2 col-4">
                <div class="card radius-12 border-0 shadow-sm text-center">
                    <div class="card-body py-16 px-8">
                        <h6 class="fw-bold mb-0">{{ number_format($count) }}</h6>
                        <span class="text-secondary-light text-xs">{{ $label }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Admins --}}
        <div class="card radius-12 border-0 shadow-sm mb-24">
            <div class="card-header bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                <h6 class="text-lg fw-semibold mb-0">School Admins</h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addAdminForm">Add Admin</button>
            </div>
            <div id="addAdminForm" class="collapse border-bottom">
                <form method="POST" action="{{ route('platform.schools.admins.store', $organization) }}" class="row g-2 p-24">
                    @csrf
                    <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
                    <div class="col-md-4"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                    <div class="col-md-3"><input type="text" name="password" class="form-control" placeholder="Password (min 8)" required minlength="8"></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Create</button></div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-24">Name</th>
                                <th>Email</th>
                                <th>Last login</th>
                                <th class="text-end pe-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($organization->admins as $admin)
                            <tr>
                                <td class="ps-24 fw-medium">{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td class="text-secondary-light text-sm">{{ $admin->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                                <td class="text-end pe-24">
                                    <form method="POST" action="{{ route('platform.schools.impersonate', [$organization, $admin]) }}"
                                          onsubmit="return confirm('Log in as {{ $admin->name }}? You will enter their school panel.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:login-2-linear"></iconify-icon> Login as
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-24 text-secondary-light">No admins yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Activity --}}
        <div class="card radius-12 border-0 shadow-sm">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Activity</h6></div>
            <div class="card-body py-16 px-24">
                @forelse($activity as $log)
                <div class="d-flex align-items-start gap-3 py-8 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <iconify-icon icon="solar:record-circle-linear" class="text-primary-600 mt-1"></iconify-icon>
                    <div class="flex-grow-1">
                        <span class="fw-medium">{{ $log->description }}</span>
                        <div class="text-secondary-light text-sm">{{ $log->admin?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <p class="text-secondary-light mb-0">No activity recorded for this school yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-xxl-4">
        {{-- Subscription --}}
        <div class="card radius-12 border-0 shadow-sm mb-24">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Subscription</h6></div>
            <div class="card-body p-24">
                @if($organization->currentSubscription)
                    @php $sub = $organization->currentSubscription; @endphp
                    <div class="d-flex align-items-center justify-content-between mb-12">
                        <span class="fw-semibold text-lg">{{ $sub->plan?->name ?? 'Custom' }}</span>
                        <span class="badge platform-badge {{ $sub->status?->badgeClass() }}">{{ $sub->status?->label() }}</span>
                    </div>
                    @if($sub->plan)
                        <p class="text-secondary-light mb-8">{{ $sub->plan->formattedPrice() }} / {{ $sub->plan->billing_interval }}</p>
                    @endif
                    <ul class="list-unstyled text-sm text-secondary-light mb-0">
                        @if($sub->trial_ends_at)<li>Trial ends: {{ $sub->trial_ends_at->format('d M Y') }}</li>@endif
                        @if($sub->current_period_end)<li>Current period ends: {{ $sub->current_period_end->format('d M Y') }}</li>@endif
                        @if($sub->stripe_subscription_id)<li>Stripe: <code>{{ $sub->stripe_subscription_id }}</code></li>@endif
                    </ul>
                @else
                    <p class="text-secondary-light mb-0">No active subscription.</p>
                @endif
            </div>
        </div>

        {{-- Status control --}}
        <div class="card radius-12 border-0 shadow-sm mb-24">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Account Status</h6></div>
            <div class="card-body p-24">
                <form method="POST" action="{{ route('platform.schools.status', $organization) }}">
                    @csrf
                    <select name="status" class="form-select mb-12">
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected($organization->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary w-100">Update Status</button>
                </form>
                <p class="text-secondary-light text-xs mt-12 mb-0">
                    Suspended / inactive / cancelled schools are locked out of their admin panel (billing stays reachable).
                </p>
            </div>
        </div>

        {{-- Contact --}}
        <div class="card radius-12 border-0 shadow-sm">
            <div class="card-header bg-base py-16 px-24"><h6 class="text-lg fw-semibold mb-0">Contact</h6></div>
            <div class="card-body p-24">
                <ul class="list-unstyled mb-0 d-flex flex-column gap-2 text-sm">
                    <li><strong>Contact:</strong> {{ $organization->contact_name ?? '—' }}</li>
                    <li><strong>Email:</strong> {{ $organization->email ?? '—' }}</li>
                    <li><strong>Phone:</strong> {{ $organization->phone ?? '—' }}</li>
                    <li><strong>Website:</strong> {{ $organization->website ?? '—' }}</li>
                    <li><strong>Country:</strong> {{ $organization->country ?? '—' }}</li>
                    <li><strong>Timezone:</strong> {{ $organization->timezone }}</li>
                    @if($organization->notes)<li><strong>Notes:</strong> {{ $organization->notes }}</li>@endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
