@extends('layouts.landing')

@section('title', ($cms['seo']['meta_title'] ?? null) ?: 'Al Rushd Online School — Where Young Minds Thrive')

@section('content')
@php
    $b = $cms['branding'] ?? [];
    $nav = $cms['navbar'] ?? [];
    $hero = $cms['hero'] ?? [];
    $about = $cms['about'] ?? [];
    $features = $cms['features'] ?? [];
    $formsSec = $cms['forms_section'] ?? [];
    $statsSec = $cms['statistics'] ?? [];
    $programsSec = $cms['programs'] ?? [];
    $hiw = $cms['how_it_works'] ?? [];
    $testi = $cms['testimonials'] ?? [];
    $faqSec = $cms['faq'] ?? [];
    $contactSec = $cms['contact'] ?? [];
    $cta = $cms['cta'] ?? [];
    $footer = $cms['footer'] ?? [];
    $social = $cms['social'] ?? [];
    $gallery = $cms['gallery'] ?? [];
    $sectionsOrder = $cms['sections_order'] ?? [
        'hero', 'statistics', 'about', 'features', 'forms_section',
        'how_it_works', 'programs', 'gallery', 'testimonials', 'faq', 'contact', 'cta',
    ];
@endphp

<div class="lp-scroll-progress" id="lpScrollProgress" aria-hidden="true"></div>

{{-- ── Navbar ── --}}
<nav class="lp-nav" id="lpNav" aria-label="Main navigation">
    <div class="lp-container lp-nav-inner">
        <a href="#home" class="lp-nav-brand">
            <img src="{{ $b['logo'] ?? asset('frontend/assets/img/logo.png') }}" alt="{{ $b['company_name'] ?? 'Al Rushd' }}" class="lp-nav-logo" loading="eager">
            <span class="lp-nav-logo-text">{{ $b['short_name'] ?? ($b['company_name'] ?? 'Al Rushd') }}</span>
        </a>

        <ul class="lp-nav-menu" id="lpNavMenu">
            @foreach(($nav['menu_items'] ?? []) as $item)
                @if($item['enabled'] ?? true)
                <li><a href="{{ $item['href'] }}" class="lp-nav-link" data-section="{{ ltrim($item['href'], '#') }}">{{ $item['label'] }}</a></li>
                @endif
            @endforeach
            <li class="lp-nav-dropdown">
                <button type="button" class="lp-nav-link lp-nav-dropdown-trigger" aria-expanded="false" aria-haspopup="true">
                    Forms <i class="fa fa-chevron-down lp-nav-chevron" aria-hidden="true"></i>
                </button>
                <div class="lp-nav-dropdown-menu">
                    @foreach ($formCards as $card)
                    <a href="{{ url($card['href']) }}" class="lp-nav-dropdown-item">
                        <i class="fa {{ $card['icon'] }}"></i> {{ $card['label'] }}
                    </a>
                    @endforeach
                </div>
            </li>
            <li class="lp-nav-mobile-actions">
                <a href="{{ $nav['login_url'] ?? '/admin/login' }}" class="lp-btn lp-btn--outline-dark">{{ $nav['login_text'] ?? 'Login' }}</a>
                <a href="{{ $nav['apply_url'] ?? '#forms' }}" class="lp-btn lp-btn--primary">{{ $nav['apply_text'] ?? 'Apply Now' }}</a>
            </li>
        </ul>

        <div class="lp-nav-actions">
            @if($nav['show_search'] ?? true)
            <button type="button" class="lp-nav-search" id="lpSearchBtn" aria-label="Search forms">
                <i class="fa fa-search"></i>
            </button>
            @endif
            @if($nav['show_login'] ?? true)
            <a href="{{ $nav['login_url'] ?? '/admin/login' }}" class="lp-nav-login">{{ $nav['login_text'] ?? 'Login' }}</a>
            @endif
            @if($nav['show_apply'] ?? true)
            <a href="{{ $nav['apply_url'] ?? '#forms' }}" class="lp-btn lp-btn--primary lp-nav-cta">{{ $nav['apply_text'] ?? 'Apply Now' }}</a>
            @endif
            <button type="button" class="lp-nav-toggle" id="lpNavToggle" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

{{-- Search overlay --}}
<div class="lp-search-overlay" id="lpSearchOverlay">
    <div class="lp-search-box">
        <input type="text" class="lp-search-input" id="lpSearchInput" placeholder="Search forms..." autocomplete="off">
        <div class="lp-search-results" id="lpSearchResults"></div>
    </div>
</div>

@include('frontend.partials.landing-sections')

{{-- ── Footer ── --}}
<footer class="lp-footer">
    <div class="lp-container">
        <div class="lp-footer-grid">
            <div class="lp-footer-brand">
                <img src="{{ $b['footer_logo'] ?? ($b['logo'] ?? asset('frontend/assets/img/logo.png')) }}" alt="{{ $b['company_name'] ?? 'Al Rushd' }}" loading="lazy">
                <p>{{ $footer['description'] ?? '' }}</p>
                <div class="lp-footer-social">
                    @if(($social['enabled']['facebook'] ?? false) && !empty($social['facebook']))
                    <a href="{{ $social['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    @endif
                    @if(($social['enabled']['twitter'] ?? false) && !empty($social['twitter']))
                    <a href="{{ $social['twitter'] }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    @endif
                    @if(($social['enabled']['instagram'] ?? false) && !empty($social['instagram']))
                    <a href="{{ $social['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    @endif
                    @if(($social['enabled']['linkedin'] ?? false) && !empty($social['linkedin']))
                    <a href="{{ $social['linkedin'] }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    @endif
                </div>
            </div>
            <div>
                <h4 class="lp-footer-title">Quick Links</h4>
                <ul class="lp-footer-links">
                    @foreach(($footer['quick_links'] ?? []) as $link)
                    <li><a href="{{ $link['href'] }}">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="lp-footer-title">Admissions</h4>
                <ul class="lp-footer-links">
                    @foreach (array_slice($formCards, 0, 5) as $card)
                    <li><a href="{{ url($card['href']) }}">{{ $card['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="lp-footer-title">Stay Updated</h4>
                <p style="font-size:14px;color:rgba(255,255,255,0.55);margin:0 0 8px">Subscribe to our newsletter for news and updates.</p>
                <form class="lp-footer-newsletter" onsubmit="event.preventDefault();alert('Thank you for subscribing!');">
                    <input type="email" placeholder="Your email" required aria-label="Email for newsletter">
                    <button type="submit">Subscribe</button>
                </form>
                <ul class="lp-footer-links" style="margin-top:20px">
                    <li><a href="mailto:{{ $landing['contact']['email'] }}">{{ $landing['contact']['email'] }}</a></li>
                    <li><a href="tel:{{ str_replace(' ', '', $landing['contact']['phone']) }}">{{ $landing['contact']['phone'] }}</a></li>
                </ul>
            </div>
        </div>
        <div class="lp-footer-bottom">
            <span>{{ $b['copyright'] ?? '© '.date('Y').' Al Rushd Online School.' }}</span>
            <div class="lp-footer-legal">
                <a href="{{ $footer['privacy_url'] ?? '#' }}">Privacy Policy</a>
                <a href="{{ $footer['terms_url'] ?? '#' }}">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<button type="button" class="lp-back-top" id="lpBackTop" aria-label="Back to top">
    <i class="fa fa-arrow-up"></i>
</button>

@endsection

@section('script-before')
<script>
    window.__lpFormCards = @json(collect($formCards)->map(fn($c) => ['label' => $c['label'], 'href' => url($c['href']), 'description' => $c['description']]));
</script>
@endsection
