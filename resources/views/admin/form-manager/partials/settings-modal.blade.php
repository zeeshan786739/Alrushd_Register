<div class="modal fade" id="formSettingsModal" tabindex="-1" aria-labelledby="formSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered fc-settings-dialog">
        <div class="modal-content radius-12 border-0 shadow-lg">
            <form method="POST" id="formSettingsForm">
                @csrf
                @method('PUT')

                <div class="modal-header border-bottom px-24 py-16">
                    <div>
                        <h6 class="modal-title fw-semibold mb-4" id="formSettingsModalLabel">Display Settings</h6>
                        <p class="text-secondary-light text-sm mb-0" id="formSettingsSubtitle">Choose where this form appears on the website.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-24 py-20">
                    <p class="text-secondary-light text-sm mb-16">Tap an option to select or deselect. You can choose more than one.</p>
                    <div class="fc-settings-options d-flex flex-column gap-12">
                        @foreach($placementOptions as $key => $option)
                        <label class="fc-settings-option" for="placement_{{ $key }}" data-settings-option>
                            <input
                                type="checkbox"
                                class="fc-settings-checkbox"
                                name="placements[]"
                                value="{{ $key }}"
                                id="placement_{{ $key }}"
                                data-placement="{{ $key }}"
                            >
                            <span class="fc-settings-option-icon">
                                <iconify-icon icon="{{ $option['icon'] ?? 'solar:widget-linear' }}"></iconify-icon>
                            </span>
                            <span class="fc-settings-option-body">
                                <span class="fw-semibold d-block">{{ $option['label'] }}</span>
                                <span class="text-secondary-light text-sm">{{ $option['description'] }}</span>
                            </span>
                            <span class="fc-settings-option-check" aria-hidden="true">
                                <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer border-top px-24 py-16 gap-10">
                    <button type="button" class="btn btn-outline-neutral-500 radius-8 px-20 py-11" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-24 py-11 fc-btn">
                        <iconify-icon icon="solar:diskette-linear"></iconify-icon>
                        <span>Save Settings</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
