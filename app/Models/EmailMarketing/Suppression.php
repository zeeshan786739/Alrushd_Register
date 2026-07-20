<?php

namespace App\Models\EmailMarketing;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Suppression extends Model
{
    use BelongsToOrganization;

    protected $table = 'em_suppressions';

    protected $fillable = [
        'organization_id', 'email', 'reason', 'token', 'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return ['unsubscribed_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $suppression): void {
            if (! $suppression->token) {
                $suppression->token = Str::random(48);
            }
            $suppression->email = strtolower(trim($suppression->email));
        });
    }
}
