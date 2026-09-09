<div class="crm-overview-modules">
    <div class="crm-overview-modules__head">
        <h2 class="crm-overview-modules__title">CRM modules</h2>
        <p class="crm-overview-modules__sub">Jump into a workspace</p>
    </div>
    <div class="crm-overview-modules__grid">
        @foreach($quickLinks as $link)
            <a href="{{ $link['url'] }}" class="crm-source-card crm-source-card--all">
                <span class="crm-source-card__icon"><iconify-icon icon="{{ $link['icon'] }}"></iconify-icon></span>
                <span class="crm-source-card__label">{{ $link['label'] }}</span>
            </a>
        @endforeach
        @can('view form submissions')
            <a href="{{ route('admin.crm.form-entries.index') }}" class="crm-source-card crm-source-card--forms">
                <span class="crm-source-card__icon"><iconify-icon icon="solar:inbox-in-linear"></iconify-icon></span>
                <span class="crm-source-card__label">Forms</span>
                @if(($formStats['submissions_pending'] ?? 0) > 0)
                    <strong class="crm-source-card__count">{{ number_format($formStats['submissions_pending']) }}</strong>
                @endif
            </a>
        @endcan
    </div>
</div>
