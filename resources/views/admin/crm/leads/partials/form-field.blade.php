@php
    $label = $label ?? '';
    $name = $name ?? '';
    $type = $type ?? 'text';
    $value = $value ?? '';
    $required = $required ?? false;
    $wide = ! empty($wide);
    $inputClass = trim('crm-lead-form-field__input form-control '.($inputClass ?? ''));
@endphp
<label class="crm-lead-form-field {{ $wide ? 'crm-lead-form-field--wide' : '' }}">
    <span class="crm-lead-form-field__label">{{ $label }}@if($required) *@endif</span>
    @if(($type ?? 'text') === 'textarea')
        <textarea name="{{ $name }}" class="{{ $inputClass }}" rows="{{ $rows ?? 4 }}" @if($required) required @endif @if(!empty($placeholder)) placeholder="{{ $placeholder }}" @endif>{{ $value }}</textarea>
    @else
        <input type="{{ $type }}"
               name="{{ $name }}"
               class="{{ $inputClass }}"
               value="{{ $value }}"
               @if($required) required @endif
               @if(!empty($placeholder)) placeholder="{{ $placeholder }}" @endif
               autocomplete="off">
    @endif
</label>
