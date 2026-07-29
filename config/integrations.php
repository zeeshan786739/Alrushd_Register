<?php

return [

    'meta' => [
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'webhook_verify_token' => env('META_WEBHOOK_VERIFY_TOKEN'),
        'graph_version' => env('META_GRAPH_VERSION', 'v21.0'),
        'oauth_scopes' => [
            'pages_manage_metadata',
            'pages_read_engagement',
            'leads_retrieval',
        ],
    ],

    'platforms' => [
        'facebook' => [
            'label' => 'Facebook Lead Ads',
            'icon' => 'logos:facebook',
            'available' => true,
        ],
        'tiktok' => [
            'label' => 'TikTok Lead Generation',
            'icon' => 'logos:tiktok-icon',
            'available' => false,
        ],
    ],

];
