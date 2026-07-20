<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class OrganizationContext
{
    public static function id(): ?int
    {
        return Auth::guard('admin')->user()?->organization_id;
    }

    public static function idOrFail(): int
    {
        $id = self::id();

        if (! $id) {
            abort(403, 'Organization context is required.');
        }

        return $id;
    }
}
