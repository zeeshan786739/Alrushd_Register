@php
    $admissionTags = $admissionTags ?? \App\Support\LeadAdmissionTags::forLead($lead, $limit ?? 6);
    $compact = $compact ?? false;
@endphp
@if($admissionTags !== [])
    <div class="crm-admission-tags {{ $compact ? 'crm-admission-tags--compact' : '' }}" aria-label="Admission details">
        @foreach($admissionTags as $tag)
            @if($compact && !empty($tag['short']))
                <span class="crm-admission-tag crm-admission-tag--{{ $tag['tone'] }} crm-admission-tag--mini"
                      title="{{ ($tag['title'] ?? $tag['label']).': '.($tag['value'] ?? $tag['label']) }}">
                    <iconify-icon icon="{{ $tag['icon'] }}"></iconify-icon>
                    <span class="crm-admission-tag__label">{{ $tag['short'] }}</span>
                    @if(!empty($tag['value']))
                        <span class="crm-admission-tag__dot crm-admission-tag__dot--{{ $tag['tone'] === 'success' ? 'done' : 'pending' }}"></span>
                    @endif
                </span>
            @else
                <span class="crm-admission-tag crm-admission-tag--{{ $tag['tone'] }}" title="{{ $tag['title'] ?? $tag['label'] }}">
                    <iconify-icon icon="{{ $tag['icon'] }}"></iconify-icon>
                    <span class="crm-admission-tag__label">{{ $tag['short'] ?? $tag['label'] }}</span>
                    @if(!empty($tag['value']))
                        <span class="crm-admission-tag__value">{{ $tag['value'] }}</span>
                    @endif
                </span>
            @endif
        @endforeach
    </div>
@endif
