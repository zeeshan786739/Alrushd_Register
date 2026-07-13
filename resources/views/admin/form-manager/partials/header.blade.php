<div class="d-flex flex-wrap align-items-start justify-content-between gap-16 mb-24">
    <div class="flex-grow-1">
        <h6 class="fw-semibold mb-0">{{ $title }}</h6>
        @if(!empty($subtitle))
            <p class="text-secondary-light text-sm mb-0 mt-8">{{ $subtitle }}</p>
        @endif
        <ul class="d-flex align-items-center flex-wrap gap-2 mb-0 mt-12 list-unstyled text-sm">
            <li class="fw-medium">
                <a href="{{ route('admin.dashboard') }}" class="d-inline-flex align-items-center gap-1 hover-text-primary text-secondary-light fc-btn">
                    <iconify-icon icon="solar:home-smile-angle-outline"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li class="text-secondary-light">/</li>
            <li class="fw-medium">
                <a href="{{ route('admin.form-manager.index') }}" class="hover-text-primary text-secondary-light">Form Center</a>
            </li>
            @if(!empty($breadcrumbs))
                @foreach($breadcrumbs as $crumb)
                    <li class="text-secondary-light">/</li>
                    <li class="fw-medium">
                        @if(!empty($crumb['url']))
                            <a href="{{ $crumb['url'] }}" class="hover-text-primary text-secondary-light">{{ $crumb['label'] }}</a>
                        @else
                            <span class="text-primary-600">{{ $crumb['label'] }}</span>
                        @endif
                    </li>
                @endforeach
            @elseif(!empty($breadcrumb))
                <li class="text-secondary-light">/</li>
                <li class="fw-medium text-primary-600">{{ $breadcrumb }}</li>
            @endif
        </ul>
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

@if(session('success'))
<div class="alert alert-success bg-success-focus text-success-main border-0 radius-8 mb-24 d-flex align-items-center gap-8">
    <iconify-icon icon="solar:check-circle-linear" class="text-xl flex-shrink-0"></iconify-icon>
    <span>{{ session('success') }}</span>
</div>
@endif
