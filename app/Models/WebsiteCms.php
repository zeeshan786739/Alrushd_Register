<?php

namespace App\Models;

use App\Support\OrganizationContext;
use App\Support\PublicOrganizationContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteCms extends Model
{
    protected $table = 'website_cms';

    protected $fillable = [
        'organization_id',
        'draft',
        'published',
        'version_history',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'draft' => 'array',
        'published' => 'array',
        'version_history' => 'array',
        'published_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public static function instance(): self
    {
        return static::forOrganization(static::resolveOrganizationId());
    }

    public static function forOrganization(?int $organizationId): self
    {
        $organizationId ??= static::resolveOrganizationId();

        return static::query()->firstOrCreate(
            ['organization_id' => $organizationId],
            ['draft' => [], 'published' => [], 'version_history' => []]
        );
    }

    private static function resolveOrganizationId(): int
    {
        if ($org = PublicOrganizationContext::get()) {
            return $org->id;
        }

        if ($adminOrgId = OrganizationContext::id()) {
            return $adminOrgId;
        }

        return Organization::default()->id;
    }
}
