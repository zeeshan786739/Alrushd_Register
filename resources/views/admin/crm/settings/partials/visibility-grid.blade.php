<div class="crm-doc-vis-grid">
    @foreach($fields as $key => $label)
        @php $fieldId = 'crm-doc-vis-'.$prefix.'-'.$key; @endphp
        <label class="crm-doc-vis-chip" for="{{ $fieldId }}">
            <input type="checkbox" class="crm-doc-vis-chip__input" id="{{ $fieldId }}" name="{{ $prefix }}[{{ $key }}]" value="1" @checked($values[$key] ?? false)>
            <span class="crm-doc-vis-chip__inner">
                <span class="crm-doc-vis-chip__icon" aria-hidden="true"><iconify-icon icon="solar:check-read-linear"></iconify-icon></span>
                <span>{{ $label }}</span>
            </span>
        </label>
    @endforeach
</div>
