@php
    $options = $options ?? [];
    $value = $value ?? '';
    $field = $field ?? '';
    $recordId = $recordId ?? '';
    $idAttr = $idAttr ?? 'data-record-id';
    $owner = ! empty($owner);
    $ariaLabel = $ariaLabel ?? $field;
    $current = $options[(string) $value] ?? null;
    if (! $current && $value === '' && isset($options[''])) {
        $current = $options[''];
    }
    $tone = $current['tone'] ?? \App\Support\CrmStatusTone::for((string) $value);
    $icon = $current['icon'] ?? ($owner ? 'solar:user-linear' : \App\Support\CrmStatusTone::icon((string) $value));
    $label = $current['label'] ?? ($value === '' || $value === null ? 'Unassigned' : (string) $value);
@endphp
<div class="crm-inline-control {{ $owner ? 'crm-inline-control--owner' : '' }}"
     data-crm-inline
     data-field="{{ $field }}"
     {{ $idAttr }}="{{ $recordId }}"
     data-previous="{{ $value }}"
     data-tone="{{ $tone }}"
     data-icon="{{ $icon }}">
    <button type="button"
            class="crm-inline-trigger"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-label="{{ $ariaLabel }}">
        <iconify-icon class="crm-inline-trigger__icon" icon="{{ $icon }}"></iconify-icon>
        <span class="crm-inline-trigger__label">{{ $label }}</span>
        <iconify-icon class="crm-inline-trigger__chevron" icon="solar:alt-arrow-down-linear"></iconify-icon>
    </button>
    <div class="crm-inline-menu" role="listbox" hidden>
        @foreach($options as $optValue => $opt)
            @php
                $optTone = $opt['tone'] ?? \App\Support\CrmStatusTone::for((string) $optValue);
                $optIcon = $opt['icon'] ?? ($owner ? 'solar:user-linear' : \App\Support\CrmStatusTone::icon((string) $optValue));
                $optLabel = $opt['label'] ?? (string) $optValue;
                $selected = (string) $optValue === (string) $value;
            @endphp
            <button type="button"
                    class="crm-inline-option {{ $selected ? 'is-selected' : '' }}"
                    role="option"
                    data-value="{{ $optValue }}"
                    data-tone="{{ $optTone }}"
                    data-icon="{{ $optIcon }}"
                    data-label="{{ $optLabel }}"
                    aria-selected="{{ $selected ? 'true' : 'false' }}">
                <iconify-icon icon="{{ $optIcon }}"></iconify-icon>
                <span class="crm-inline-option__label">{{ $optLabel }}</span>
                @if($selected)
                    <iconify-icon class="crm-inline-option__check" icon="solar:check-circle-bold"></iconify-icon>
                @endif
            </button>
        @endforeach
    </div>
</div>
