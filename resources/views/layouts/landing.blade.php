<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $seo = ($cms ?? [])['seo'] ?? []; $brand = ($cms ?? [])['branding'] ?? []; @endphp
    <title>@yield('title', $seo['meta_title'] ?? ($brand['browser_title'] ?? 'Al Rushd Online School'))</title>
    <meta name="description" content="{{ $seo['meta_description'] ?? ($brand['website_description'] ?? '') }}">
    <meta name="keywords" content="{{ $seo['meta_keywords'] ?? '' }}">
    @if(!empty($seo['canonical_url']))<link rel="canonical" href="{{ $seo['canonical_url'] }}">@endif

    <link href="{{ $brand['favicon'] ?? asset('frontend/assets/img/logo.png') }}" rel="icon">
    @php
        $typo = ($cms ?? [])['typography'] ?? [];
        $fonts = array_unique(array_filter([
            $typo['heading_font'] ?? 'Libre Baskerville',
            $typo['body_font'] ?? 'Inter',
            $typo['button_font'] ?? null,
        ]));
        $fontQuery = implode('|', array_map(fn ($f) => str_replace(' ', '+', $f), $fonts));
    @endphp
    @if($fontQuery)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $fontQuery }}&display=swap" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="{{ asset('frontend/assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('frontend/assets/css/landing.css') }}" rel="stylesheet">
    @if(!empty($themeCss))<style>{!! $themeCss !!}</style>@endif
    @if(!empty($cms['custom_code']['custom_css'] ?? ''))<style>{!! ($cms ?? [])['custom_code']['custom_css'] ?? '' !!}</style>@endif
    @yield('css')
    @if(!empty($cms['analytics']['google_tag_manager'] ?? '')){!! $cms['analytics']['google_tag_manager'] !!}@endif
    @if(!empty($cms['custom_code']['head_scripts'] ?? '')){!! $cms['custom_code']['head_scripts'] !!}@endif
</head>
<body class="lp-body">

@yield('content')

<script src="{{ asset('frontend/assets/vendor/aos/aos.js') }}"></script>
<script src="{{ asset('frontend/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
@yield('script-before')
<script src="{{ asset('frontend/assets/js/landing.js') }}"></script>
@if(!empty($cms['custom_code']['custom_js'] ?? ''))<script>{!! $cms['custom_code']['custom_js'] !!}</script>@endif
@if(!empty($cms['custom_code']['footer_scripts'] ?? '')){!! $cms['custom_code']['footer_scripts'] !!}@endif
@yield('script')
</body>
</html>
