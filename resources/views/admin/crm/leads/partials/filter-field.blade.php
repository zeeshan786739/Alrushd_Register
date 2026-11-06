@php
    $key = $filter['key'];
    $label = $filter['label'];
    $notSet = \App\Support\LeadFormOptions::FILTER_NOT_SET;
    $choices = match ($key) {
        'follow_up' => \App\Support\LeadSettings::FOLLOW_UP_OPTIONS,
        'contact_details' => \App\Support\LeadSettings::CONTACT_DETAIL_OPTIONS,
        'children' => \App\Support\LeadSettings::CHILDREN_OPTIONS,
        'converted' => \App\Support\LeadSettings::CONVERTED_OPTIONS,
        default => null,
    };
@endphp
@if($key === 'campaign_name')
    <div class="crm-lead-finder__refine-field crm-lead-finder__refine-field--wide">
        <label for="campaign_name">{{ $label }}</label>
        <div class="crm-lead-finder__campaign-row">
            <input type="text" name="campaign_name" id="campaign_name" class="form-control" placeholder="e.g. Year 7 Open Day" value="{{ request('campaign_name') }}">
            <button type="submit" class="crm-filter-workspace__btn crm-filter-workspace__btn--primary">Apply</button>
        </div>
    </div>
@else
    <div class="crm-lead-finder__refine-field">
        <label for="{{ $key }}">{{ $label }}</label>
        <select name="{{ $filter['param'] }}" id="{{ $key }}" class="form-select" data-crm-filter-auto-submit>
            @switch($key)
                @case('lead_status')
                    <option value="">Any status</option>
                    @foreach(\App\Support\LeadStatusCatalog::ordered() as $status)
                        <option value="{{ $status->value }}" data-tone="{{ $status->tone }}" data-icon="{{ \App\Support\CrmStatusTone::icon($status->value) }}" @selected(\App\Enums\LeadStatus::canonicalize(request('lead_status')) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                    @break
                @case('year_group')
                    <option value="">Any year group</option>
                    <option value="{{ $notSet }}" @selected(request('year_group') === $notSet)>Not set</option>
                    @foreach($yearGroupFilterOptions ?? [] as $option)
                        <option value="{{ $option }}" @selected(request('year_group') === $option)>{{ $option }}</option>
                    @endforeach
                    @break
                @case('admission_intake')
                    <option value="">Any intake</option>
                    <option value="{{ $notSet }}" @selected(request('admission_intake') === $notSet)>Not set yet</option>
                    @foreach($admissionIntakeFilterOptions ?? [] as $option)
                        <option value="{{ $option }}" @selected(request('admission_intake') === $option)>{{ $option }}</option>
                    @endforeach
                    @break
                @case('assigned_to')
                    <option value="">Anyone</option>
                    <option value="me" @selected(request('assigned_to') === 'me' || (string) request('assigned_to') === (string) $currentAdminId)>Me</option>
                    <option value="unassigned" @selected(request('assigned_to') === 'unassigned')>Unassigned</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" @selected((string) request('assigned_to') === (string) $admin->id && request('assigned_to') !== 'me')>{{ $admin->name }}</option>
                    @endforeach
                    @break
                @case('lead_category_id')
                    <option value="">Any category</option>
                    @foreach($categoryFilterOptions ?? [] as $optValue => $optLabel)
                        <option value="{{ $optValue }}" @selected(request('lead_category_id') == $optValue)>{{ $optLabel }}</option>
                    @endforeach
                    @break
                @case('contact_method')
                    <option value="">Any method</option>
                    @foreach($contactMethodFilterOptions ?? [] as $option)
                        <option value="{{ $option }}" @selected(request('contact_method') === $option)>{{ $option }}</option>
                    @endforeach
                    @break
                @default
                    @if($filter['custom'])
                        <option value="">Any</option>
                        <option value="{{ $notSet }}" @selected(request($filter['param']) === $notSet)>Not set</option>
                        @foreach(\App\Support\LeadSettings::customFilter(substr($key, strlen(\App\Support\LeadSettings::CUSTOM_PARAM_PREFIX)))['options'] ?? [] as $option)
                            <option value="{{ $option }}" @selected(request($filter['param']) === $option)>{{ $option }}</option>
                        @endforeach
                    @else
                        <option value="">{{ $key === 'follow_up' ? 'Any time' : 'Any' }}</option>
                        @foreach($choices ?? [] as $optValue => $optLabel)
                            <option value="{{ $optValue }}" @selected(request($key) === $optValue)>{{ $optLabel }}</option>
                        @endforeach
                    @endif
            @endswitch
        </select>
    </div>
@endif
