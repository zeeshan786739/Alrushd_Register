<div class="crm-lead-child" data-crm-child>
    <label class="crm-lead-form-field">
        <span class="crm-lead-form-field__label form-label" data-crm-child-label>{{ $label }}</span>
        <select name="{{ $name }}" class="{{ $selectClass }}">
            <option value="">Select…</option>
            @foreach($options as $option)
                <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $option }}</option>
            @endforeach
        </select>
    </label>
    <button type="button" class="crm-lead-child__remove" data-crm-child-remove aria-label="Remove this child" title="Remove this child" @unless($removable) hidden @endunless>
        <iconify-icon icon="solar:trash-bin-minimalistic-linear" aria-hidden="true"></iconify-icon>
    </button>
</div>
