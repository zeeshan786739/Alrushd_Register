<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SaaS platform (Enrolliq) configuration
    |--------------------------------------------------------------------------
    | Marketing/branding defaults for the platform layer. Most values can be
    | overridden at runtime from Super Admin → Settings (platform_settings).
    */

    'name' => env('SAAS_NAME', 'Enrolliq'),

    'support_email' => env('SAAS_SUPPORT_EMAIL', 'hello@enrolliq.com'),

    // When set, the SaaS landing page is also served at this domain's root "/".
    // The tenant school site keeps working at every other host.
    'domain' => env('SAAS_DOMAIN'),

    // Platform-level Stripe account (the SaaS owner's account, used to bill
    // schools). Distinct from per-tenant Stripe keys used by schools to
    // collect fees from parents. DB platform_settings override these.
    'stripe' => [
        'key' => env('PLATFORM_STRIPE_KEY'),
        'secret' => env('PLATFORM_STRIPE_SECRET'),
        'webhook_secret' => env('PLATFORM_STRIPE_WEBHOOK_SECRET'),
    ],
];
