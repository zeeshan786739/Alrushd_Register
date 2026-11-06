@php
    $yearGroup = (string) ($yearGroup ?? '');
    $extras = array_values(array_filter((array) ($extras ?? []), fn ($year) => is_string($year) && $year !== ''));
    $options = array_values(array_unique(array_merge($options ?? \App\Support\LeadFormOptions::yearGroups(), array_filter([$yearGroup]), $extras)));
    $selectClass = $selectClass ?? 'crm-lead-form-field__input form-select';
    $max = \App\Support\LeadFormOptions::MAX_CHILDREN;
    $rows = array_merge([$yearGroup], $extras);
@endphp
<div class="crm-lead-children" data-crm-children data-max="{{ $max }}">
    {{-- Always sent, so removing the last extra child is saved as a removal. --}}
    <input type="hidden" name="extra_year_groups[]" value="">
    <div class="crm-lead-children__list" data-crm-children-list>
        @foreach($rows as $index => $value)
            @include('admin.crm.leads.partials.child-year-group-row', [
                'name' => $index === 0 ? 'year_group' : 'extra_year_groups[]',
                'label' => count($rows) > 1 ? 'Child '.($index + 1).' year group' : 'Year group',
                'value' => $value,
                'options' => $options,
                'selectClass' => $selectClass,
                'removable' => count($rows) > 1,
            ])
        @endforeach
    </div>
    <template data-crm-child-template>
        @include('admin.crm.leads.partials.child-year-group-row', [
            'name' => 'extra_year_groups[]',
            'label' => 'Year group',
            'value' => '',
            'options' => $options,
            'selectClass' => $selectClass,
            'removable' => true,
        ])
    </template>
    <button type="button" class="crm-lead-children__add" data-crm-child-add @if(count($rows) >= $max) hidden @endif>
        <iconify-icon icon="solar:add-circle-linear" aria-hidden="true"></iconify-icon>
        Add another child
        <span class="crm-lead-children__hint">up to {{ $max }}</span>
    </button>
</div>
