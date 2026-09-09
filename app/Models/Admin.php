<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    public const INVITATION_PLACEHOLDER_NAME = 'Invited teammate';

    protected $guard = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'organization_id',
        'is_platform_admin',
        'last_login_at',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function isPlatformAdmin(): bool
    {
        return (bool) $this->is_platform_admin;
    }

    public function scopeForCurrentOrganization($query)
    {
        $organizationId = auth('admin')->user()?->organization_id;

        if (! $organizationId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('organization_id', $organizationId);
    }

    /** Teammates who finished invitation setup and can own leads. */
    public function scopeAssignable($query)
    {
        return $query->where('name', '!=', self::INVITATION_PLACEHOLDER_NAME);
    }

    public function hasAcceptedInvitation(): bool
    {
        return trim((string) $this->name) !== self::INVITATION_PLACEHOLDER_NAME;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
}