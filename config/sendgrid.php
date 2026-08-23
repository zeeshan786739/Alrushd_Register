<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SendGrid (global delivery provider)
    |--------------------------------------------------------------------------
    |
    | API key and webhook secrets MUST stay in environment variables.
    | Never store the API key in the database or organization settings UI.
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
