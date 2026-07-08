@extends('admin.layouts.app')
@section('title') Dashboard @endsection
@section('content')

@php
    use Carbon\Carbon;
    use App\Models\User;
    use App\Models\Student;
    use App\Models\Order;

    $students = Student::count();
    $users = User::count();
    $orders = Order::sum('amount');
    $userIncrease = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
    $studentIncrease = Student::where('created_at', '>=', Carbon::now()->subDays(7))->count();
    $lastWeekOrders = Order::where('created_at', '>=', Carbon::now()->subDays(7))->sum('amount');
@endphp

<div class="dashboard-main-body">

    @include('admin.partials.page-header', [
        'title' => 'Dashboard',
        'subtitle' => 'Overview of users, students, revenue, and key CRM metrics.',
        'hideFlash' => true,
    ])

    <div class="row gy-4">
        <div class="col-xxl-4 col-sm-6">
            <div class="card p-3 shadow-2 radius-12 border-0 input-form-light h-100 bg-gradient-end-1 fc-stat-card">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">
                        <div class="d-flex align-items-center gap-12">
                            <span class="mb-0 w-48-px h-48-px bg-primary-600 flex-shrink-0 text-white d-flex justify-content-center align-items-center rounded-circle">
                                <iconify-icon icon="solar:users-group-rounded-bold" class="icon text-xl"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 fw-medium text-secondary-light text-sm d-block">Users</span>
                                <h4 class="fw-bold mb-0">{{ number_format($users) }}</h4>
                            </div>
                        </div>
                        <div id="new-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0 text-secondary-light">
                        <span class="fc-badge fc-badge-success">+{{ $userIncrease }}</span> new this week
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-sm-6">
            <div class="card p-3 shadow-2 radius-12 border-0 input-form-light h-100 bg-gradient-end-2 fc-stat-card">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">
                        <div class="d-flex align-items-center gap-12">
                            <span class="mb-0 w-48-px h-48-px bg-success-main flex-shrink-0 text-white d-flex justify-content-center align-items-center rounded-circle">
                                <iconify-icon icon="solar:square-academic-cap-bold" class="icon text-xl"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 fw-medium text-secondary-light text-sm d-block">Students</span>
                                <h4 class="fw-bold mb-0">{{ number_format($students) }}</h4>
                            </div>
                        </div>
                        <div id="active-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0 text-secondary-light">
                        <span class="fc-badge fc-badge-success">+{{ $studentIncrease }}</span> new this week
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-sm-6">
            <div class="card p-3 shadow-2 radius-12 border-0 input-form-light h-100 bg-gradient-end-3 fc-stat-card">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">
                        <div class="d-flex align-items-center gap-12">
                            <span class="mb-0 w-48-px h-48-px bg-yellow text-white flex-shrink-0 d-flex justify-content-center align-items-center rounded-circle">
                                <iconify-icon icon="solar:wallet-money-bold" class="icon text-xl"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 fw-medium text-secondary-light text-sm d-block">Revenue</span>
                                <h4 class="fw-bold mb-0">${{ number_format($orders, 2) }}</h4>
                            </div>
                        </div>
                        <div id="total-sales-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0 text-secondary-light">
                        <span class="fc-badge fc-badge-primary">${{ number_format($lastWeekOrders, 2) }}</span> this week
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-sm-6">
            <div class="card p-3 shadow-2 radius-12 border-0 input-form-light h-100 bg-gradient-end-4 fc-stat-card">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">
                        <div class="d-flex align-items-center gap-12">
                            <span class="mb-0 w-48-px h-48-px bg-purple text-white flex-shrink-0 d-flex justify-content-center align-items-center rounded-circle">
                                <iconify-icon icon="solar:chart-2-bold" class="icon text-xl"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 fw-medium text-secondary-light text-sm d-block">Conversion</span>
                                <h4 class="fw-bold mb-0">25%</h4>
                            </div>
                        </div>
                        <div id="conversion-user-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0 text-secondary-light">
                        <span class="fc-badge fc-badge-success">+5%</span> vs last week
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-sm-6">
            <div class="card p-3 shadow-2 radius-12 border-0 input-form-light h-100 bg-gradient-end-5 fc-stat-card">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">
                        <div class="d-flex align-items-center gap-12">
                            <span class="mb-0 w-48-px h-48-px bg-pink text-white flex-shrink-0 d-flex justify-content-center align-items-center rounded-circle">
                                <iconify-icon icon="solar:target-bold" class="icon text-xl"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 fw-medium text-secondary-light text-sm d-block">Leads</span>
                                <h4 class="fw-bold mb-0">250</h4>
                            </div>
                        </div>
                        <div id="leads-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0 text-secondary-light">
                        <span class="fc-badge fc-badge-success">+20</span> new this week
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-sm-6">
            <div class="card p-3 shadow-2 radius-12 border-0 input-form-light h-100 bg-gradient-end-6 fc-stat-card">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-8">
                        <div class="d-flex align-items-center gap-12">
                            <span class="mb-0 w-48-px h-48-px bg-cyan text-white flex-shrink-0 d-flex justify-content-center align-items-center rounded-circle">
                                <iconify-icon icon="solar:dollar-minimalistic-bold" class="icon text-xl"></iconify-icon>
                            </span>
                            <div>
                                <span class="mb-2 fw-medium text-secondary-light text-sm d-block">Total Profit</span>
                                <h4 class="fw-bold mb-0">$300,700</h4>
                            </div>
                        </div>
                        <div id="total-profit-chart" class="remove-tooltip-title rounded-tooltip-value"></div>
                    </div>
                    <p class="text-sm mb-0 text-secondary-light">
                        <span class="fc-badge fc-badge-success">+$15k</span> vs last week
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
