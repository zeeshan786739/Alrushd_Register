@php
    $formEntry = $formEntry ?? null;
    $limit = (int) ($limit ?? 4);
    if (! $formEntry) {
        return;
    }
    $data = is_array($formEntry->data) ? $formEntry->data : [];
    $fields = $data['fields'] ?? $data;
    $fields = collect($fields)->filter(fn ($value) => $value !== null && $value !== '')->take($limit);
@endphp
@if($fields->isNotEmpty())
    <dl class="crm-form-preview">
        @foreach($fields as $key => $value)
            <div class="crm-form-preview__row">
                <dt>{{ is_string($key) ? str_replace('_', ' ', ucfirst($key)) : 'Field' }}</dt>
                <dd>{{ is_array($value) ? json_encode($value) : $value }}</dd>
            </div>
        @endforeach
    </dl>
@else
    <p class="crm-form-preview__empty">No submission fields recorded.</p>
@endif
