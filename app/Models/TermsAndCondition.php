<?php

namespace App\Models;

use App\Support\OrganizationContext;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class TermsAndCondition extends Model
{
    use BelongsToOrganization;

    protected $guarded = [];

    public static function current(): self
    {
        $organizationId = OrganizationContext::id();

        if ($organizationId) {
            return static::firstOrCreate(
                ['organization_id' => $organizationId],
                [
                    'terms_description' => 'Please read and accept our enrollment terms before submitting your application.',
                    'form_description' => 'By submitting this form you agree to our terms and conditions.',
                ]
            );
        }

        return static::query()->firstOrCreate([]);
    }
}
