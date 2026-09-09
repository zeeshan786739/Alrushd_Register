<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') — {{ \App\Models\PlatformSetting::get('platform_name', config('saas.name')) }} Super Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        try {
            document.documentElement.dataset.theme = localStorage.getItem('theme') === 'dark' ? 'dark' : 'light';
        } catch (_) { /* keep light */ }
    </script>
    <link rel="icon" type="image/png" href="{{ asset('frontend/assets/img/logo.png') }}" sizes="16x16" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/remixicon.css" />
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/lib/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/lib/apexcharts.css" />
    <link rel="stylesheet" href="{{ asset('admin/') }}/assets/css/lib/dataTables.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ \App\Support\AdminAsset::url('admin/assets/css/style.css') }}" />
    <link rel="stylesheet" href="{{ \App\Support\AdminAsset::url('admin/assets/css/alrushad-overrides.css') }}" />
    <link rel="stylesheet" href="{{ \App\Support\AdminAsset::url('admin/assets/css/admin-dark.css') }}" />
    <style>
        .platform-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600;
        }
        .platform-brand-chip {
            background: #254d7f;
            color: #fff; font-size: 10px; letter-spacing: .08em; text-transform: uppercase;
            padding: 2px 8px; border-radius: 6px; font-weight: 700;
        }
        html[data-theme="dark"] .platform-brand-chip {
            background: #254d7f;
            color: #fff;
        }
        .kpi-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 26px;
        }
        html[data-theme="dark"] .platform-badge.bg-success-focus { background: #183d32 !important; color: #91e5b9 !important; }
        html[data-theme="dark"] .platform-badge.bg-info-focus { background: #213d60 !important; color: #afd3ff !important; }
        html[data-theme="dark"] .platform-badge.bg-warning-focus { background: #453921 !important; color: #f4d68e !important; }
        html[data-theme="dark"] .platform-badge.bg-danger-focus { background: #472b35 !important; color: #ffb6bf !important; }
        html[data-theme="dark"] .platform-badge.bg-neutral-200 { background: #2b3648 !important; color: #cbd5e1 !important; }
        html[data-theme="dark"] .btn-sm.btn-outline-primary,
        html[data-theme="dark"] .btn-sm.btn-outline-success { border-width: 1px; }
    </style>
    @yield('css')
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
                    <button type="button" data-theme-toggle class="crm-nav-btn" title="Toggle light / dark mode" aria-label="Toggle light / dark mode">
                        <iconify-icon icon="solar:sun-2-bold" class="crm-theme-icon" width="22" height="22"></iconify-icon>
                    </button>
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
                                    <a class="dropdown-item text-black px-0 py-8 hover-bg-transparent hover-text-danger d-flex align-items-center gap-3"
                                       href="{{ route('admin.logout') }}">
                                        <iconify-icon icon="lucide:power" class="icon text-xl"></iconify-icon> Log Out
                                    </a>
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

<script>
(function () {
    const toneColors = {
        primary: '#2563eb',
        success: '#16a34a',
        warning: '#d97706',
        danger: '#ef4444',
        question: '#2563eb',
        info: '#2563eb',
    };

    window.platformConfirmSubmit = function (form) {
        const icon = form.getAttribute('data-confirm-icon') || 'question';
        const tone = form.getAttribute('data-confirm-tone') || icon;
        const title = form.getAttribute('data-confirm-title') || 'Are you sure?';
        const text = form.getAttribute('data-confirm-text') || '';
        const confirmText = form.getAttribute('data-confirm-label') || 'Confirm';
        const confirmColor = toneColors[tone] || toneColors.primary;

        if (typeof Swal === 'undefined') {
            if (window.confirm(title + (text ? '\n\n' + text : ''))) {
                form.dataset.confirmAccepted = '1';
                HTMLFormElement.prototype.submit.call(form);
            }
            return;
        }

        Swal.fire({
            title: title,
            text: text || undefined,
            icon: ['success', 'error', 'warning', 'info', 'question'].includes(icon) ? icon : 'question',
            showCancelButton: true,
            reverseButtons: true,
            focusCancel: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#94a3b8',
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel',
        }).then(function (result) {
            if (result.isConfirmed) {
                form.dataset.confirmAccepted = '1';
                HTMLFormElement.prototype.submit.call(form);
            }
        });
    };

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) {
            return;
        }
        if (form.dataset.confirmAccepted === '1') {
            delete form.dataset.confirmAccepted;
            return;
        }
        event.preventDefault();
        window.platformConfirmSubmit(form);
    });
})();
</script>

<style>
    .swal2-popup { font-family: Inter, system-ui, sans-serif; border-radius: 16px !important; }
    html[data-theme="light"] .swal2-title { font-size: 1.25rem !important; font-weight: 700 !important; color: #0f172a !important; }
    html[data-theme="light"] .swal2-html-container { font-size: 0.95rem !important; color: #475569 !important; }
    html[data-theme="dark"] .swal2-title { color: #e7edf5 !important; }
    html[data-theme="dark"] .swal2-html-container { color: #afbdd0 !important; }
</style>

@if(session('success'))
<script>
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 3000, timerProgressBar: true });
</script>
@endif
@if(session('error'))
<script>
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: @json(session('error')), showConfirmButton: false, timer: 5000, timerProgressBar: true });
</script>
@endif
@if(session('mail_warning'))
<script>
    Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: @json(session('mail_warning')), showConfirmButton: false, timer: 6000, timerProgressBar: true });
</script>
@endif

@yield('script')

</body>

</html>
