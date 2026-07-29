<?php

namespace App\Enums;

enum IntegrationPlatform: string
{
    case Facebook = 'facebook';
    case TikTok = 'tiktok';

    public function label(): string
    {
        return match ($this) {
            self::Facebook => 'Facebook Lead Ads',
            self::TikTok => 'TikTok Lead Generation',
        };
    }
}
