@php
    use App\Support\UserManagementHelper;
    $umStats = $stats ?? UserManagementHelper::stats();
    $activeTab = $activeTab ?? '';
@endphp

@once
    @include('admin.crm.partials.styles')
    @include('admin.crm.partials.workspace-shell')
    @include('admin.role-permission.partials.premium-styles')
@endonce

@include('admin.partials.page-header', [
    'title' => $shellTitle ?? 'Team & Access',
    'subtitle' => $shellSubtitle ?? 'Invite teammates, define roles, and control who can do what in your school.',
    'showBreadcrumb' => true,
    'breadcrumbs' => [['label' => 'People & Access'], ['label' => $shellTitle ?? 'Team & Access']],
    'actions' => $shellActions ?? [],
    'hideFlash' => true,
])

<div class="um-workspace crm-workspace-shell">
    @if(empty($compact))
        <div class="crm-metrics-strip" aria-label="Team and access metrics">
            <div class="crm-metrics-strip__items">
                <span class="crm-metrics-strip__hint">People &amp; Access workspace</span>
                @canany(['view user','create user','edit user'])
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="crm-metrics-strip__item">
                        <span class="crm-metrics-strip__label">Team</span>
                        <strong>{{ $umStats['users'] }}</strong>
                    </span>
                @endcanany
                @canany(['view role','create role','edit role'])
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="crm-metrics-strip__item">
                        <span class="crm-metrics-strip__label">Roles</span>
                        <strong>{{ $umStats['roles'] }}</strong>
                    </span>
                @endcanany
                @canany(['view permission','create permission','edit permission'])
                    <span class="crm-metrics-strip__sep" aria-hidden="true"></span>
                    <span class="crm-metrics-strip__item">
                        <span class="crm-metrics-strip__label">Permissions</span>
                        <strong>{{ $umStats['permissions'] }}</strong>
                    </span>
                @endcanany
            </div>
        </div>

        <div class="um-tabs-workspace">
            <div class="um-tabs-workspace__head">
                <h2 class="um-tabs-workspace__title">Workspace sections</h2>
                <p class="um-tabs-workspace__sub">Overview, team members, roles, and permissions</p>
            </div>
            @include('admin.role-permission.partials.module-nav', ['activeTab' => $activeTab, 'umStats' => $umStats])
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 m-3 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger bg-danger-focus text-danger-main border-0 radius-8 m-3 d-flex align-items-center gap-8">
            <iconify-icon icon="solar:close-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
            <span>{{ session('error') }}</span>
        </div>
    @endif
</div>
