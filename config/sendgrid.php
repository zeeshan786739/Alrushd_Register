<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SendGrid (global delivery provider)
    |--------------------------------------------------------------------------
    |
    | Default API key comes from the environment. Platform owner settings
    | (Super Admin → Settings) may override it for SaaS transactional mail
    | via PlatformMailService. Tenant email marketing still prefers per-org
    | mailbox keys where configured.
    |
    */
    'api_key' => env('SENDGRID_API_KEY'),
    'api_base' => env('SENDGRID_API_BASE', 'https://api.sendgrid.com'),
    'event_webhook_public_key' => env('SENDGRID_EVENT_WEBHOOK_PUBLIC_KEY'),
    'inbound_basic_user' => env('SENDGRID_INBOUND_BASIC_USER'),
    'inbound_basic_pass' => env('SENDGRID_INBOUND_BASIC_PASS'),
    'inbound_domain' => env('SENDGRID_INBOUND_DOMAIN'),
    /*
    | Prefer SendGrid when configured. In testing / log / array mailers,
    | EmailDeliveryService still uses the Laravel mail bridge.
    */
    'prefer_sendgrid' => filter_var(env('SENDGRID_PREFER', true), FILTER_VALIDATE_BOOL),
];
