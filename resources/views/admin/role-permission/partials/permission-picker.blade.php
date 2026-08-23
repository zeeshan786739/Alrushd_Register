@php
    use App\Support\UserManagementHelper;
    $selected = $selected ?? old('permissions', []);
    $inputName = $inputName ?? 'permissions[]';
    $totalPermissions = $permissions->count();
@endphp

<div class="um-perm-picker" data-perm-picker>
    <div class="um-perm-picker__toolbar">
        <div class="um-search-bar um-search-bar--wide">
            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
            <input type="search"
                   class="form-control radius-8"
                   placeholder="Search permissions…"
                   aria-label="Search permissions"
                   data-perm-search>
        </div>
        <div class="um-perm-picker__meta">
            <span data-perm-selected-count>{{ count((array) $selected) }}</span>
            <span>of {{ $totalPermissions }} selected</span>
        </div>
    </div>

    <div class="um-perm-picker__groups">
        @foreach($groupedPermissions as $group => $groupPermissions)
        <details class="um-perm-group" open data-perm-group>
            <summary class="um-perm-group__head">
                <span class="um-perm-group__title">
                    <iconify-icon icon="{{ UserManagementHelper::groupIcon($group) }}"></iconify-icon>
                    {{ $group }}
                </span>
                <span class="um-perm-group__actions">
                    <span class="um-perm-group__count">{{ $groupPermissions->count() }}</span>
                    <button type="button" class="um-perm-group__toggle" data-perm-select-group>Select all</button>
                </span>
            </summary>
            <div class="um-perm-grid um-perm-grid--compact">
                @foreach($groupPermissions as $permission)
                <label class="um-perm-item"
                       for="permission_{{ $permission->id }}"
                       data-perm-label="{{ strtolower($permission->name) }}">
                    <input type="checkbox"
                           class="form-check-input flex-shrink-0 mt-1"
                           id="permission_{{ $permission->id }}"
                           name="{{ $inputName }}"
                           value="{{ $permission->name }}"
                           data-perm-checkbox
                           {{ in_array($permission->name, (array) $selected, true) ? 'checked' : '' }}>
                    <span class="um-perm-item__text">
                        <span class="um-action-badge {{ UserManagementHelper::actionBadgeClass(UserManagementHelper::permissionAction($permission->name)) }}">
                            {{ UserManagementHelper::permissionAction($permission->name) }}
                        </span>
                        <span class="text-sm">{{ UserManagementHelper::formatPermissionName($permission->name) }}</span>
                    </span>
                </label>
                @endforeach
            </div>
        </details>
        @endforeach
    </div>
</div>

@error('permissions')
<div class="text-danger text-sm mt-8">{{ $message }}</div>
@enderror
