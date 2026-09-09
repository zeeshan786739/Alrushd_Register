@php
    use App\Support\UserManagementHelper;
    $selected = $selected ?? old('permissions', []);
    $inputName = $inputName ?? 'permissions[]';
    $totalPermissions = $permissions->count();
@endphp

<div class="um-perm-picker um-perm-picker--premium" data-perm-picker>
    <div class="um-filter-workspace um-perm-picker__filter">
        <div class="um-ai-search">
            <div class="um-ai-search__head">
                <span class="um-ai-search__badge"><iconify-icon icon="solar:magnifer-linear"></iconify-icon> Find</span>
                <span class="um-perm-picker__meta">
                    <strong data-perm-selected-count>{{ count((array) $selected) }}</strong>
                    <span>of {{ $totalPermissions }} selected</span>
                </span>
            </div>
            <div class="um-ai-search__shell">
                <span class="um-ai-search__icon"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                <input type="search"
                       class="um-ai-search__input"
                       placeholder="Search permissions…"
                       aria-label="Search permissions"
                       data-perm-search>
            </div>
        </div>
    </div>

    <div class="um-perm-picker__catalog">
        <div class="um-perm-catalog" data-perm-catalog>
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
                <div class="um-perm-chip-grid um-perm-chip-grid--picker">
                    @foreach($groupPermissions as $permission)
                    <label class="um-perm-chip um-perm-chip--picker"
                           for="permission_{{ $permission->id }}"
                           data-perm-label="{{ strtolower($permission->name) }}">
                        <input type="checkbox"
                               class="um-perm-chip__check"
                               id="permission_{{ $permission->id }}"
                               name="{{ $inputName }}"
                               value="{{ $permission->name }}"
                               data-perm-checkbox
                               {{ in_array($permission->name, (array) $selected, true) ? 'checked' : '' }}>
                        <span class="um-action-badge {{ UserManagementHelper::actionBadgeClass(UserManagementHelper::permissionAction($permission->name)) }}">
                            {{ UserManagementHelper::permissionAction($permission->name) }}
                        </span>
                        <span class="um-perm-chip__label">{{ UserManagementHelper::formatPermissionName($permission->name) }}</span>
                        <span class="um-perm-chip__tick" aria-hidden="true"><iconify-icon icon="solar:check-circle-bold"></iconify-icon></span>
                    </label>
                    @endforeach
                </div>
            </details>
            @endforeach
        </div>
    </div>
</div>

@error('permissions')
<div class="text-danger text-sm um-form-card__error">{{ $message }}</div>
@enderror
