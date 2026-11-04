<?php

namespace App\Support;

use Illuminate\Http\Request;

final class ScalarQuery
{
    /**
     * Filter pages expect single values. A crafted URL such as ?search[]=x would
     * otherwise reach string-only code and views and return a server error.
     */
    public static function strip(Request $request): void
    {
        foreach ($request->query->all() as $key => $value) {
            if (is_array($value)) {
                $request->query->remove($key);
            }
        }
    }
}
