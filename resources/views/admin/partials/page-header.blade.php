<div class="crm-page-header">
    <div class="flex-grow-1">
        <h1 class="crm-page-header__title">{{ $title }}</h1>
        @if(!empty($subtitle))
            <p class="crm-page-header__subtitle">{{ $subtitle }}</p>
        @endif
        @if(!empty($breadcrumbs) || !empty($showBreadcrumb))
        <ul class="crm-breadcrumb">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline"></iconify-icon>
                    Dashboard
                </a>
            </li>
            @if(!empty($breadcrumbs))
                @foreach($breadcrumbs as $crumb)
                    <li>/</li>
                    <li @if(empty($crumb['url'])) class="is-current" @endif>
                        @if(!empty($crumb['url']))
                            <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                        @else
                            {{ $crumb['label'] }}
                        @endif
                    </li>
                @endforeach
            @endif
        </ul>
        @endif
    </div>
    @if(!empty($actions))
        <div class="d-flex flex-wrap align-items-center gap-10">
            @foreach($actions as $action)
                <a href="{{ $action['url'] }}" class="btn {{ $action['class'] ?? 'btn-outline-neutral-500 radius-8 px-20 py-11' }} fc-btn">
                    @if(!empty($action['icon']))
                        <iconify-icon icon="{{ $action['icon'] }}"></iconify-icon>
                    @endif
                    <span>{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    @endif
</div>

@if(session('success') && empty($hideFlash))
<div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 mb-24 d-flex align-items-center gap-8">
    <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error') && empty($hideFlash))
<div class="alert alert-danger bg-danger-focus text-danger-main border-0 radius-8 mb-24 d-flex align-items-center gap-8">
    <iconify-icon icon="solar:close-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
    <span>{{ session('error') }}</span>
</div>
@endif
