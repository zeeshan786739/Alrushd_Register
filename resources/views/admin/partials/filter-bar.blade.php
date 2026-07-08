<form method="GET" action="{{ $action }}" class="crm-filter-bar mb-24">
    <div class="crm-filter-bar__grid">
        @foreach($fields as $field)
            <div class="crm-filter-field">
                <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                @if(($field['type'] ?? 'text') === 'date')
                    <input type="date"
                           name="{{ $field['name'] }}"
                           id="{{ $field['name'] }}"
                           class="form-control radius-8"
                           value="{{ request($field['name']) }}">
                @elseif(($field['type'] ?? 'text') === 'select')
                    <select name="{{ $field['name'] }}" id="{{ $field['name'] }}" class="form-select radius-8">
                        <option value="">{{ $field['placeholder'] ?? 'All' }}</option>
                        @foreach($field['options'] ?? [] as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request($field['name']) == $optValue)>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text"
                           name="{{ $field['name'] }}"
                           id="{{ $field['name'] }}"
                           class="form-control radius-8"
                           placeholder="{{ $field['placeholder'] ?? '' }}"
                           value="{{ request($field['name']) }}">
                @endif
            </div>
        @endforeach
        <div class="crm-filter-actions">
            <button type="submit" class="btn btn-primary-600 radius-8 px-20 py-11 fc-btn">
                <iconify-icon icon="solar:filter-linear"></iconify-icon>
                <span>Apply</span>
            </button>
            @if(!empty($resetUrl))
                <a href="{{ $resetUrl }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-11 fc-btn">
                    <iconify-icon icon="solar:restart-linear"></iconify-icon>
                    <span>Reset</span>
                </a>
            @endif
        </div>
    </div>
</form>
