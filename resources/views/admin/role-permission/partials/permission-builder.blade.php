@php
    use App\Support\UserManagementHelper;
    $selectedAction = old('action', $selectedAction ?? '');
    $selectedModule = old('module', $selectedModule ?? '');
    $selectedResource = old('resource', $selectedResource ?? '');
    $selectedCustomResource = old('custom_resource', $selectedCustomResource ?? '');
    $manualName = old('name', $manualName ?? '');
@endphp

<div class="um-perm-builder" data-perm-builder data-existing-names='@json($existingNames ?? [])'>
    @if($errors->has('name'))
    <div class="alert alert-danger bg-danger-focus text-danger-main border-0 radius-8 mb-20 d-flex align-items-center gap-8">
        <iconify-icon icon="solar:close-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
        <span>{{ $errors->first('name') }}</span>
    </div>
    @endif

    <input type="hidden" name="action" value="{{ $selectedAction }}" data-builder-action>
    <input type="hidden" name="resource" value="{{ $selectedResource }}" data-builder-resource>
    <input type="hidden" name="module" value="{{ $selectedModule }}" data-builder-module>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="um-builder-step">
                <div class="um-builder-step__head">
                    <span class="um-builder-step__num">1</span>
                    <div>
                        <h3>What should someone be allowed to do?</h3>
                        <p>Pick the type of access — you can combine this with an area in the next step.</p>
                    </div>
                </div>
                <div class="um-action-grid">
                    @foreach($builderActions as $action)
                    <button type="button"
                            class="um-action-chip {{ $selectedAction === $action['value'] ? 'is-selected' : '' }}"
                            data-builder-action-option="{{ $action['value'] }}"
                            aria-pressed="{{ $selectedAction === $action['value'] ? 'true' : 'false' }}">
                        <span class="um-action-badge {{ UserManagementHelper::actionBadgeClass($action['value']) }}">{{ $action['label'] }}</span>
                        <span class="um-action-chip__hint">{{ $action['hint'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="um-builder-step">
                <div class="um-builder-step__head">
                    <span class="um-builder-step__num">2</span>
                    <div>
                        <h3>Which part of the platform?</h3>
                        <p>Choose the module this permission belongs to.</p>
                    </div>
                </div>
                <div class="um-module-grid">
                    @foreach($builderModules as $module)
                    <button type="button"
                            class="um-module-chip {{ $selectedModule === $module['key'] ? 'is-selected' : '' }}"
                            data-builder-module-option="{{ $module['key'] }}"
                            aria-pressed="{{ $selectedModule === $module['key'] ? 'true' : 'false' }}">
                        <iconify-icon icon="{{ $module['icon'] }}"></iconify-icon>
                        <span>{{ $module['label'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="um-builder-step">
                <div class="um-builder-step__head">
                    <span class="um-builder-step__num">3</span>
                    <div>
                        <h3>What exactly can they access?</h3>
                        <p>Select a feature from the module, or describe a custom one.</p>
                    </div>
                </div>

                <div class="um-resource-panel" data-builder-resource-panel hidden>
                    <div class="um-resource-grid" data-builder-resource-list></div>
                </div>

                <div class="um-resource-custom" data-builder-custom-resource hidden>
                    <label class="form-label fw-semibold text-sm">Custom feature name</label>
                    <input type="text"
                           class="form-control radius-8"
                           name="custom_resource"
                           value="{{ $selectedCustomResource }}"
                           placeholder="e.g. reports, fee reminders, audit log"
                           data-builder-custom-input>
                    <small class="text-secondary-light d-block mt-8">Use plain words — for example, “view reports” or “edit forms”. We format permissions automatically.</small>
                </div>

                <p class="um-builder-placeholder" data-builder-resource-placeholder>
                    Choose a platform area above to see available features.
                </p>
            </div>

            @if(!empty($builderPresets))
            <div class="um-builder-presets">
                <span class="um-builder-presets__label">Quick start:</span>
                @foreach($builderPresets as $preset)
                <button type="button"
                        class="um-preset-chip"
                        data-builder-preset="{{ $preset['action'] }}|{{ $preset['resource'] }}">
                    {{ $preset['label'] }}
                </button>
                @endforeach
            </div>
            @endif

            <details class="um-builder-advanced mt-20">
                <summary>Advanced: edit permission name directly</summary>
                <div class="um-builder-advanced__body">
                    <label class="form-label fw-semibold text-sm">Permission name</label>
                    <input type="text"
                           name="name"
                           class="form-control radius-8 @error('name') is-invalid @enderror"
                           value="{{ $manualName }}"
                           placeholder="view reports"
                           data-builder-manual-name>
                    <small class="text-secondary-light d-block mt-8">Only use this if you know the exact system permission name.</small>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </details>
        </div>

        <div class="col-xl-4">
            <div class="um-preview-card um-panel um-panel--sticky p-24">
                <span class="um-preview-card__label">Live preview</span>
                <div class="um-preview-card__name" data-builder-preview-name>
                    {{ $manualName ?: 'Choose options to build a permission' }}
                </div>
                <div class="um-preview-card__meta" data-builder-preview-meta>
                    This is what roles will show to admins.
                </div>

                <div class="um-preview-card__status" data-builder-preview-status data-state="idle">
                    <iconify-icon icon="solar:info-circle-linear"></iconify-icon>
                    <span>Complete the steps to preview your permission.</span>
                </div>

                <div class="um-preview-card__tips">
                    <strong>Next step after saving</strong>
                    <p>Open <a href="{{ route('admin.roles.index') }}">Roles</a>, edit a role, and tick this permission for the right teammates.</p>
                </div>

                <div class="d-flex flex-column gap-10 mt-24">
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn w-100" data-builder-submit>
                        <iconify-icon icon="solar:check-circle-linear"></iconify-icon>
                        <span>Create permission</span>
                    </button>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn w-100">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" data-builder-modules-json>@json($builderModules)</script>
</div>
