@php
    $name = $name ?? '';
    $value = (string) ($value ?? '');
    $options = $options ?? [];
    $required = $required ?? false;
    $owner = ! empty($owner);
    $ariaLabel = $ariaLabel ?? $name;
    $current = $options[$value] ?? $options[''] ?? null;
    $tone = $current['tone'] ?? 'neutral';
    $icon = $current['icon'] ?? ($owner ? 'solar:user-linear' : 'solar:menu-dots-linear');
    $label = $current['label'] ?? $value;
@endphp
<label class="crm-form-tag-select {{ $owner ? 'crm-form-tag-select--owner' : '' }}"
       data-crm-form-tag-select
       data-tone="{{ $tone }}">
    <select name="{{ $name }}"
            class="crm-form-tag-select__native"
            aria-label="{{ $ariaLabel }}"
            @if($required) required @endif>
        @foreach($options as $optValue => $opt)
            @php
                $selected = (string) $optValue === $value;
                $optTone = $opt['tone'] ?? \App\Support\CrmStatusTone::for((string) $optValue);
                $optIcon = $opt['icon'] ?? ($owner ? 'solar:user-linear' : \App\Support\CrmStatusTone::icon((string) $optValue));
            @endphp
            <option value="{{ $optValue }}"
                    @selected($selected)
                    data-tone="{{ $optTone }}"
                    data-icon="{{ $optIcon }}"
                    data-label="{{ $opt['label'] ?? $optValue }}">
                {{ $opt['label'] ?? $optValue }}
            </option>
        @endforeach
    </select>
    <span class="crm-form-tag-select__face">
        <iconify-icon class="crm-form-tag-select__icon" icon="{{ $icon }}"></iconify-icon>
        <span class="crm-form-tag-select__label">{{ $label }}</span>
        <iconify-icon class="crm-form-tag-select__chevron" icon="solar:alt-arrow-down-linear"></iconify-icon>
    </span>
</label>
