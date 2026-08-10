<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') — {{ \App\Models\PlatformSetting::get('platform_name', config('saas.name')) }} Super Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('frontend/assets/img/logo.png') }}" sizes="16x16" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/remixicon.css" />
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/lib/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/lib/apexcharts.css" />
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/lib/dataTables.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/style.css" />
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/alrushad-overrides.css" />
    <style>
        .platform-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600;
        }
        .platform-brand-chip {
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff; font-size: 10px; letter-spacing: .08em; text-transform: uppercase;
            padding: 2px 8px; border-radius: 6px; font-weight: 700;
        }
        .kpi-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 26px;
        }
    </style>
</head>

<body>

@php
    $platformName = \App\Models\PlatformSetting::get('platform_name', config('saas.name'));
    $adminUser = auth()->guard('admin')->user();
@endphp

<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('platform.dashboard') }}" class="sidebar-logo sidebar-logo--brand" title="{{ $platformName }}">
            <img src="{{ asset('frontend/assets/img/logo.png') }}" alt="{{ $platformName }}" class="crm-logo-img" width="40" height="40">
            <span class="crm-brand-text">
                <span class="crm-brand-name">{{ $platformName }}</span>
                <span class="crm-brand-tag">Super Admin</span>
            </span>
        </a>
    </div>
    <div class="sidebar-menu-area">
        <nav aria-label="Platform primary">
            @include('platform.layouts.sidebar')
        </nav>
    </div>
</aside>

<main class="dashboard-main">
    <div class="navbar-header">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <div class="d-flex flex-wrap align-items-center gap-4">
                    <button type="button" class="sidebar-toggle" aria-label="Collapse sidebar" aria-expanded="true" title="Collapse sidebar">
                        <iconify-icon icon="solar:round-alt-arrow-left-linear" class="sidebar-toggle-icon text-2xl"></iconify-icon>
                    </button>
                    <span class="platform-brand-chip">SaaS Control Panel</span>
                </div>
            </div>
            <div class="col-auto">
                <div class="d-flex flex-wrap align-items-center gap-3 crm-navbar-actions">
                    <a href="{{ route('saas.landing') }}" target="_blank" rel="noopener" class="crm-nav-btn" title="View SaaS landing page">
                        <iconify-icon icon="mdi:web" width="22" height="22"></iconify-icon>
                    </a>
                    <div class="dropdown">
                        <button class="crm-nav-avatar" type="button" data-bs-toggle="dropdown" aria-label="Account menu">
                            <span class="crm-nav-avatar__fallback" style="display:flex">{{ strtoupper(substr($adminUser?->name ?? 'P', 0, 1)) }}</span>
                        </button>
                        <div class="dropdown-menu to-top dropdown-menu-sm">
                            <div class="py-12 px-16 radius-8 bg-primary-50 mb-16">
                                <h6 class="text-lg text-primary-light fw-semibold mb-2">{{ $adminUser?->name }}</h6>
                                <span class="text-sm text-secondary-light">Platform Owner</span>
                            </div>
                            <ul class="to-top-list">
                                <li>
                                    <form method="POST" action="{{ route('admin.logout') }}">
                                        @csrf
                                        <a onclick="event.preventDefault(); this.closest('form').submit();"
                                           class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-danger d-flex align-items-center gap-3"
                                           href="{{ route('admin.logout') }}">
                                            <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon> Log Out
                                        </a>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-main-body">
        @yield('content')
    </div>

    <footer class="d-footer">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <p class="mb-0">© {{ date('Y') }} {{ $platformName }}. All Rights Reserved.</p>
            </div>
            <div class="col-auto">
                <p class="mb-0">SaaS Platform <span class="text-primary-600">Super Admin</span></p>
            </div>
        </div>
    </footer>
</main>

@yield('modals')

<script src="{{ asset('admin/') }}/assets/js/lib/jquery-3.7.1.min.js"></script>
<script src="{{ asset('admin/') }}/assets/js/lib/bootstrap.bundle.min.js"></script>
<script src="{{ asset('admin/') }}/assets/js/lib/apexcharts.min.js"></script>
<script src="{{ asset('admin/') }}/assets/js/lib/dataTables.min.js"></script>
<script src="{{ asset('admin/') }}/assets/js/lib/iconify-icon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('admin/') }}/assets/js/app.js"></script>

@if(session('success'))
<script>
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 3000, timerProgressBar: true });
</script>
@endif
@if(session('error'))
<script>
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: @json(session('error')), showConfirmButton: false, timer: 4000, timerProgressBar: true });
</script>
@endif

@yield('script')

</body>

</html>
