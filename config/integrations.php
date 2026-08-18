<?php

return [

    'meta' => [
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'webhook_verify_token' => env('META_WEBHOOK_VERIFY_TOKEN'),
        'graph_version' => env('META_GRAPH_VERSION', 'v21.0'),
        // Scopes must match permissions added on your Meta app (Marketing API apps
        // often lack leads_retrieval / pages_manage_metadata until App Review).
        // Override via META_OAUTH_SCOPES in .env once Meta approves more permissions.
        'oauth_scopes' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env(
                'META_OAUTH_SCOPES',
                'pages_show_list,pages_read_engagement,business_management,ads_management'
            ))
        ))),
    ],

    'platforms' => [
        'facebook' => [
            'label' => 'Facebook Lead Ads',
            'icon' => 'logos:facebook',
            'available' => true,
        ],
        'tiktok' => [
            'label' => 'TikTok Lead Ads',
            'icon' => 'logos:tiktok-icon',
            'available' => true,
        ],
    ],

    'tiktok' => [
        'app_id' => env('TIKTOK_APP_ID'),
        'app_secret' => env('TIKTOK_APP_SECRET'),
        'redirect_uri' => env('TIKTOK_REDIRECT_URI'),
        // Current TikTok API for Business portal authorization. Override if the
        // Marketing API app was created under ads.tiktok.com/marketing_api/auth.
        'auth_url' => env('TIKTOK_AUTH_URL', 'https://business-api.tiktok.com/portal/auth'),
        'api_base' => env('TIKTOK_API_BASE', 'https://business-api.tiktok.com/open_api/v1.3'),
        'timeout' => (int) env('TIKTOK_HTTP_TIMEOUT', 20),
    ],

];
