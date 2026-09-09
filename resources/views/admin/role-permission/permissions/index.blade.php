@extends('admin.layouts.app')

@section('title') Permissions @endsection

@section('content')
@php
    use App\Support\UserManagementHelper;
    $shellActions = auth()->user()->can('create permission') ? [[
        'label' => 'Create permission',
        'url' => route('admin.permissions.create'),
        'class' => 'btn-primary-600 radius-8 px-20 py-11',
        'icon' => 'solar:magic-stick-3-linear',
    ]] : [];
@endphp

<div class="dashboard-main-body" id="um-workspace-page">
@include('admin.role-permission.partials.shell', [
    'activeTab' => 'permissions',
    'stats' => $stats,
    'shellTitle' => 'Permissions',
    'shellSubtitle' => 'Fine-grained access rules grouped by module. Assign these to roles — not directly to users.',
    'shellActions' => $shellActions,
])

<div class="um-info-banner">
    <iconify-icon icon="solar:info-circle-linear"></iconify-icon>
    <div>
        <strong>How permissions work</strong>
        <p>Permissions are system-defined capabilities (like “view leads” or “send invoices”). Create roles, tick the permissions each role needs, then assign roles to teammates.</p>
    </div>
</div>

<div class="um-filter-workspace">
    <div class="um-ai-search">
        <div class="um-ai-search__head">
            <span class="um-ai-search__badge"><iconify-icon icon="solar:magnifer-linear"></iconify-icon> Find</span>
            <span class="um-muted-pill">{{ $permissions->count() }} total permissions</span>
        </div>
        <div class="um-ai-search__shell">
            <span class="um-ai-search__icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
            <input type="search"
                   class="um-ai-search__input"
                   placeholder="Search permissions…"
                   aria-label="Search permissions"
                   data-perm-catalog-search>
        </div>
    </div>
</div>

<div class="crm-list-shell">
<div class="crm-leads-table crm-leads-table--permissions">
<div class="um-perm-catalog-shell">

    @if($permissions->isEmpty())
        <div class="um-empty-state um-empty-state--panel">
            <iconify-icon icon="solar:key-linear"></iconify-icon>
            <h6>No permissions found</h6>
            <p>Permissions are usually seeded automatically when the platform is set up.</p>
        </div>
    @else
        <div class="um-perm-catalog" data-perm-catalog>
            @foreach($groupedPermissions as $group => $groupPermissions)
            <details class="um-perm-group um-perm-group--readonly" open data-perm-catalog-group>
                <summary class="um-perm-group__head">
                    <span class="um-perm-group__title">
                        <iconify-icon icon="{{ UserManagementHelper::groupIcon($group) }}"></iconify-icon>
                        {{ $group }}
                    </span>
                    <span class="um-perm-group__count">{{ $groupPermissions->count() }}</span>
                </summary>
                <div class="um-perm-chip-grid">
                    @foreach($groupPermissions as $permission)
                    <div class="um-perm-chip" data-perm-name="{{ strtolower($permission->name) }}">
                        <span class="um-action-badge {{ UserManagementHelper::actionBadgeClass(UserManagementHelper::permissionAction($permission->name)) }}">
                            {{ UserManagementHelper::permissionAction($permission->name) }}
                        </span>
                        <span>{{ UserManagementHelper::formatPermissionName($permission->name) }}</span>
                        @can('edit permission')
                        <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                           class="um-perm-chip__edit"
                           title="Edit permission"
                           aria-label="Edit {{ $permission->name }}">
                            <iconify-icon icon="solar:pen-linear"></iconify-icon>
                        </a>
                        @endcan
                    </div>
                    @endforeach
                </div>
            </details>
            @endforeach
        </div>
    @endif
</div>
</div>
</div>
</div>
@endsection

@section('script')
<script>
(function () {
    var input = document.querySelector('[data-perm-catalog-search]');
    if (!input) return;
    input.addEventListener('input', function () {
        var q = input.value.toLowerCase().trim();
        document.querySelectorAll('[data-perm-catalog-group]').forEach(function (group) {
            var visible = 0;
            group.querySelectorAll('[data-perm-name]').forEach(function (chip) {
                var show = !q || (chip.getAttribute('data-perm-name') || '').includes(q);
                chip.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            group.style.display = visible ? '' : 'none';
            if (q && visible) group.open = true;
        });
    });
})();
</script>
@endsection
