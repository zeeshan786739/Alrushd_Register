@extends('platform.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
        <h6 class="fw-semibold mb-0">Platform Dashboard</h6>
        <span class="text-secondary-light text-sm">Everything happening across your SaaS.</span>
    </div>
    <a href="{{ route('platform.schools.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <iconify-icon icon="ic:baseline-plus"></iconify-icon> New School
    </a>
</div>

<div class="row gy-4 mb-24">
    <div class="col-xxl-3 col-sm-6">
        <div class="card h-100 radius-12 border-0 shadow-sm">
            <div class="card-body p-20 d-flex align-items-center gap-3">
                <div class="kpi-icon bg-primary-50 text-primary-600">
                    <iconify-icon icon="solar:buildings-2-linear"></iconify-icon>
                </div>
                <div>
                    <span class="text-secondary-light text-sm d-block">Total Schools</span>
                    <h5 class="fw-bold mb-0">{{ $totalSchools }}</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6">
        <div class="card h-100 radius-12 border-0 shadow-sm">
            <div class="card-body p-20 d-flex align-items-center gap-3">
                <div class="kpi-icon bg-success-focus text-success-main">
                    <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                </div>
                <div>
                    <span class="text-secondary-light text-sm d-block">Active / Trial</span>
                    <h5 class="fw-bold mb-0">{{ $activeSchools }} <small class="text-secondary-light fw-normal">({{ $trialSchools }} trial)</small></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6">
        <div class="card h-100 radius-12 border-0 shadow-sm">
            <div class="card-body p-20 d-flex align-items-center gap-3">
                <div class="kpi-icon bg-info-focus text-info-main">
                    <iconify-icon icon="solar:dollar-linear"></iconify-icon>
                </div>
                <div>
                    <span class="text-secondary-light text-sm d-block">Est. MRR</span>
                    <h5 class="fw-bold mb-0">${{ number_format($mrr, 2) }} <small class="text-secondary-light fw-normal">({{ $paidSubscriptions }} paid)</small></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6">
        <div class="card h-100 radius-12 border-0 shadow-sm">
            <div class="card-body p-20 d-flex align-items-center gap-3">
                <div class="kpi-icon bg-warning-focus text-warning-main">
                    <iconify-icon icon="solar:calendar-linear"></iconify-icon>
                </div>
                <div>
                    <span class="text-secondary-light text-sm d-block">Open Demo Requests</span>
                    <h5 class="fw-bold mb-0">{{ $openDemoRequests }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row gy-4">
    <div class="col-xxl-6">
        <div class="card radius-12 border-0 shadow-sm h-100">
            <div class="card-header bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                <h6 class="text-lg fw-semibold mb-0">Latest Schools</h6>
                <a href="{{ route('platform.schools.index') }}" class="text-primary-600 text-sm fw-medium">View all</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-24">School</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSchools as $school)
                            <tr>
                                <td class="ps-24">
                                    <a href="{{ route('platform.schools.show', $school) }}" class="fw-medium text-primary-600">{{ $school->name }}</a>
                                </td>
                                <td>{{ $school->currentSubscription?->plan?->name ?? '—' }}</td>
                                <td><span class="badge platform-badge {{ $school->status?->badgeClass() }}">{{ $school->status?->label() }}</span></td>
                                <td class="text-secondary-light text-sm">{{ $school->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-24 text-secondary-light">No schools yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-6">
        <div class="card radius-12 border-0 shadow-sm h-100">
            <div class="card-header bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                <h6 class="text-lg fw-semibold mb-0">Latest Demo Requests</h6>
                <a href="{{ route('platform.demo-requests.index') }}" class="text-primary-600 text-sm fw-medium">View all</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-24">Contact</th>
                                <th>Organisation</th>
                                <th>Status</th>
                                <th>Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDemoRequests as $demo)
                            <tr>
                                <td class="ps-24">
                                    <a href="{{ route('platform.demo-requests.show', $demo) }}" class="fw-medium text-primary-600">{{ $demo->name }}</a>
                                    <div class="text-secondary-light text-sm">{{ $demo->email }}</div>
                                </td>
                                <td>{{ $demo->organization_name ?? '—' }}</td>
                                <td><span class="badge platform-badge {{ $demo->status?->badgeClass() }}">{{ $demo->status?->label() }}</span></td>
                                <td class="text-secondary-light text-sm">{{ $demo->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-24 text-secondary-light">No demo requests yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card radius-12 border-0 shadow-sm">
            <div class="card-header bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">Recent Activity</h6>
            </div>
            <div class="card-body py-16 px-24">
                @forelse($recentActivity as $log)
                <div class="d-flex align-items-start gap-3 py-8 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <iconify-icon icon="solar:record-circle-linear" class="text-primary-600 mt-1"></iconify-icon>
                    <div class="flex-grow-1">
                        <span class="fw-medium">{{ $log->description }}</span>
                        <div class="text-secondary-light text-sm">
                            {{ $log->admin?->name ?? 'System' }}
                            @if($log->organization) · <a href="{{ route('platform.schools.show', $log->organization) }}" class="text-primary-600">{{ $log->organization->name }}</a> @endif
                            · {{ $log->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <code class="text-xs text-secondary-light">{{ $log->action }}</code>
                </div>
                @empty
                <p class="text-secondary-light text-center mb-0 py-16">Platform activity will appear here.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
