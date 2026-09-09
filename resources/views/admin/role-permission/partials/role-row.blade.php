@php
    use App\Support\UserManagementHelper;
    $isProtected = UserManagementHelper::isProtectedRole($role);
    $permTotal = $permTotal ?? 1;
    $coverage = min(100, round(($role->permissions->count() / max($permTotal, 1)) * 100));
    $canEdit = auth()->user()->can('edit role');
    $editUrl = route('admin.roles.edit', $role->id);
@endphp
<article
    class="crm-list-row um-list-row um-list-row--role fc-form-row {{ $isProtected ? 'um-list-row--protected' : '' }}"
    @if($canEdit)
        data-um-role-open
        data-href="{{ $editUrl }}"
        tabindex="0"
        role="link"
    @endif
    aria-label="Role {{ UserManagementHelper::formatRoleName($role->name) }}">
    <span class="crm-list-row__priority-rail" aria-hidden="true"></span>
    <div class="crm-list-row__identity">
        <span class="um-user-avatar" style="background: {{ UserManagementHelper::avatarGradient($role->name) }};" aria-hidden="true">
            {{ UserManagementHelper::initials($role->name) }}
        </span>
        <div class="crm-list-row__identity-copy min-w-0">
            <div class="crm-list-row__name-row">
                <span class="crm-list-row__name">{{ UserManagementHelper::formatRoleName($role->name) }}</span>
                @if($isProtected)
                    <span class="um-system-badge">System</span>
                @endif
            </div>
            <div class="crm-list-row__contact-line">
                <span class="crm-list-row__contact">{{ $role->name }}</span>
            </div>
        </div>
    </div>
    <div class="crm-list-row__field">
        <span class="crm-list-row__date">{{ $role->permissions->count() }}</span>
        <span class="crm-list-row__date-sub">Permissions</span>
    </div>
    <div class="crm-list-row__field">
        <span class="crm-list-row__date">{{ $role->users_count ?? 0 }}</span>
        <span class="crm-list-row__date-sub">Members</span>
    </div>
    <div class="crm-list-row__field crm-list-row__field--date">
        <span class="crm-list-row__date">{{ $coverage }}%</span>
        <span class="crm-list-row__date-sub">Coverage</span>
    </div>
    <div class="crm-list-row__actions">
        <div class="crm-list-row__action-group">
            @if($canEdit)
                <a href="{{ $editUrl }}" class="crm-list-action" onclick="event.stopPropagation()"><iconify-icon icon="solar:pen-linear"></iconify-icon></a>
            @endif
            @if(auth()->user()->can('delete role') && ! $isProtected && ($role->users_count ?? 0) === 0)
                <form id="delete-form-{{ $role->id }}" action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onclick="event.stopPropagation()">@csrf @method('DELETE')</form>
                <button type="button" class="crm-list-action is-delete delete-btn" data-id="{{ $role->id }}"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
            @endif
        </div>
        @if($canEdit)
            <a href="{{ $editUrl }}" class="crm-list-row__chevron" onclick="event.stopPropagation()"><iconify-icon icon="solar:alt-arrow-right-linear"></iconify-icon></a>
        @endif
    </div>
</article>
