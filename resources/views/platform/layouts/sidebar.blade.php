<ul class="sidebar-menu" id="sidebar-menu" aria-label="Platform navigation">

    <li>
        <a href="{{ route('platform.dashboard') }}"
           class="{{ request()->routeIs('platform.dashboard') ? 'active-page' : '' }}" title="Dashboard">
            <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Dashboard</span>
        </a>
    </li>

    <li class="sidebar-menu-group-title" role="presentation">Customers</li>

    <li>
        <a href="{{ route('platform.schools.index') }}"
           class="{{ request()->routeIs('platform.schools.*') ? 'active-page' : '' }}" title="Schools">
            <iconify-icon icon="solar:buildings-2-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Schools</span>
        </a>
    </li>

    <li>
        <a href="{{ route('platform.demo-requests.index') }}"
           class="{{ request()->routeIs('platform.demo-requests.*') ? 'active-page' : '' }}" title="Demo Requests">
            <iconify-icon icon="solar:calendar-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Demo Requests</span>
        </a>
    </li>

    <li class="sidebar-menu-group-title" role="presentation">Billing</li>

    <li>
        <a href="{{ route('platform.plans.index') }}"
           class="{{ request()->routeIs('platform.plans.*') ? 'active-page' : '' }}" title="Plans">
            <iconify-icon icon="solar:tag-price-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Plans &amp; Pricing</span>
        </a>
    </li>

    <li>
        <a href="{{ route('platform.subscriptions.index') }}"
           class="{{ request()->routeIs('platform.subscriptions.*') ? 'active-page' : '' }}" title="Subscriptions">
            <iconify-icon icon="solar:card-linear" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Subscriptions</span>
        </a>
    </li>

    <li class="sidebar-menu-group-title" role="presentation">Platform</li>

    <li>
        <a href="{{ route('saas.landing') }}" target="_blank" rel="noopener" title="Landing Page">
            <iconify-icon icon="mdi:web" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Landing Page</span>
        </a>
    </li>

    <li>
        <a href="{{ route('platform.settings.index') }}"
           class="{{ request()->routeIs('platform.settings.*') ? 'active-page' : '' }}" title="Settings">
            <iconify-icon icon="icon-park-outline:setting-two" class="menu-icon" aria-hidden="true"></iconify-icon>
            <span>Settings</span>
        </a>
    </li>

</ul>
