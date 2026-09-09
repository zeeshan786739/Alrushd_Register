@php
    $activeTab = $activeTab ?? 'overview';
    $sections = [
        ['key' => 'overview', 'label' => 'Overview', 'icon' => 'solar:widget-2-linear', 'tone' => 'overview', 'url' => route('admin.account.index')],
        ['key' => 'profile', 'label' => 'Profile', 'icon' => 'solar:user-linear', 'tone' => 'profile', 'url' => route('admin.account.profile')],
        ['key' => 'security', 'label' => 'Security', 'icon' => 'solar:shield-keyhole-linear', 'tone' => 'security', 'url' => route('admin.account.security')],
        ['key' => 'payments', 'label' => 'Payments', 'icon' => 'solar:wallet-money-linear', 'tone' => 'payments', 'url' => route('admin.account.payments.edit')],
        ['key' => 'website', 'label' => 'Website', 'icon' => 'solar:globus-linear', 'tone' => 'website', 'url' => route('admin.account.website.edit')],
        ['key' => 'billing', 'label' => 'Billing', 'icon' => 'solar:card-linear', 'tone' => 'billing', 'url' => route('admin.billing.index')],
    ];
@endphp
<nav class="acct-module-nav" aria-label="Account sections" role="tablist">
    @foreach($sections as $section)
        @php $isActive = $activeTab === $section['key']; @endphp
        <a href="{{ $section['url'] }}"
           @class(['crm-source-card', 'crm-source-card--'.$section['tone'], 'is-active' => $isActive])
           role="tab"
           @if($isActive) aria-selected="true" @else aria-selected="false" @endif>
            <span class="crm-source-card__icon" aria-hidden="true">
                <iconify-icon icon="{{ $section['icon'] }}"></iconify-icon>
            </span>
            <span class="crm-source-card__label">{{ $section['label'] }}</span>
        </a>
    @endforeach
</nav>
