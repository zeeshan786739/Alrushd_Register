<?php

namespace App\Enums;

enum IntegrationConnectionStatus: string
{
    case Connected = 'connected';
    case Disconnected = 'disconnected';
    case Error = 'error';
    case Pending = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::Connected => 'Connected',
            self::Disconnected => 'Not connected',
            self::Error => 'Connection error',
            self::Pending => 'Pending setup',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Connected => 'bg-success-focus text-success-main',
            self::Disconnected => 'bg-neutral-200 text-secondary-light',
            self::Error => 'bg-danger-focus text-danger-main',
            self::Pending => 'bg-warning-focus text-warning-main',
        };
    }
}
