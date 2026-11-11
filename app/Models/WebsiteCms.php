<?php

namespace App\Models;

use App\Support\OrganizationContext;
use App\Support\PublicOrganizationContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
        'mode',
        'external_url',
        'redirect_legacy',
        'last_revalidated_at',
        'last_revalidate_status',
    ];

    protected $hidden = ['api_token', 'api_token_hash'];

    public const MODE_BUILTIN = 'builtin';

    public const MODE_HEADLESS = 'headless';

    protected $casts = [
        'draft' => 'array',
        'published' => 'array',
        'version_history' => 'array',
        'published_at' => 'datetime',
        'api_token' => 'encrypted',
        'redirect_legacy' => 'boolean',
        'last_revalidated_at' => 'datetime',
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

    public function isHeadless(): bool
    {
        return $this->mode === self::MODE_HEADLESS && filled($this->external_url);
    }

    /** Base URL of the connected front end, without a trailing slash. */
    public function externalBase(): ?string
    {
        return $this->external_url ? rtrim($this->external_url, '/') : null;
    }

    /** Issue a new API token (returned once in plain text; stored encrypted + hashed). */
    public function rotateApiToken(): string
    {
        $token = 'wst_'.Str::random(48);
        $this->api_token = $token;
        $this->api_token_hash = hash('sha256', $token);
        $this->save();

        return $token;
    }

    public static function findByApiToken(?string $token): ?self
    {
        if (! $token || strlen($token) < 20) {
            return null;
        }

        return static::query()->where('api_token_hash', hash('sha256', $token))->first();
    }

    /** True when the legacy built-in landing page should send visitors to the new site. */
    public function shouldRedirectLegacy(): bool
    {
        return $this->isHeadless() && $this->redirect_legacy;
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
