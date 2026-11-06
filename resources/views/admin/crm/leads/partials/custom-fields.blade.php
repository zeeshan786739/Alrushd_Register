@foreach(\App\Support\LeadSettings::customFilters() as $customFilter)
    @php $customValue = old('custom_fields.'.$customFilter['key'], ($values ?? [])[$customFilter['key']] ?? ''); @endphp
    @include('admin.crm.leads.partials.form-field', [
        'label' => $customFilter['label'],
        'name' => 'custom_fields['.$customFilter['key'].']',
        'type' => 'select',
        'value' => $customValue,
        'options' => \App\Support\LeadFormOptions::withCurrent($customFilter['options'], $customValue),
    ])
@endforeach
