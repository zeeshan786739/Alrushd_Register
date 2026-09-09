@php
    $type = $type ?? 'text';
    $rows = $rows ?? 2;
    $inputId = $inputId ?? 'crm-doc-'.md5($name);
    $toggleId = $toggleId ?? $inputId.'-toggle';
@endphp
<div class="crm-doc-field">
    <div class="crm-doc-field__head">
        <label class="crm-doc-field__label" for="{{ $inputId }}">{{ $label }}</label>
        @if(! empty($toggleName))
            <label class="crm-doc-toggle" for="{{ $toggleId }}">
                <input type="checkbox" class="crm-doc-toggle__input" id="{{ $toggleId }}" name="{{ $toggleName }}" value="1" @checked($toggleChecked ?? false)>
                <span class="crm-doc-toggle__track" aria-hidden="true"></span>
                <span class="crm-doc-toggle__text">{{ $toggleLabel ?? 'Show on document' }}</span>
            </label>
        @endif
    </div>
    @if($type === 'textarea')
        <textarea id="{{ $inputId }}" name="{{ $name }}" class="form-control" rows="{{ $rows }}">{{ $value ?? '' }}</textarea>
    @else
        <input type="{{ $type }}" id="{{ $inputId }}" name="{{ $name }}" class="form-control" value="{{ $value ?? '' }}">
    @endif
    @if(! empty($errorKey) && $errors->has($errorKey))
        <div class="text-danger-600">{{ $errors->first($errorKey) }}</div>
    @endif
</div>
