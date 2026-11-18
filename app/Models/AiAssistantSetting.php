<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Website CMS → AI assistant settings for one school. The API key is encrypted and never sent to the browser. */
class AiAssistantSetting extends Model
{
    protected $fillable = ['organization_id', 'enabled', 'api_key', 'model', 'base_url', 'limit_type', 'monthly_limit'];

    protected $hidden = ['api_key'];

    protected $casts = [
        'enabled' => 'boolean',
        'api_key' => 'encrypted',
        'monthly_limit' => 'float',
    ];

    public static function forOrganization(int $organizationId): self
    {
        return self::firstOrNew(['organization_id' => $organizationId], ['enabled' => false, 'limit_type' => 'tokens']);
    }
}
