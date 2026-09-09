<?php

namespace App\Support;

use Illuminate\Http\Request;

final class CrmLeadFilterUrl
{
    /** @param  array<string, mixed>  $merge
     * @param  list<string>  $except
     */
    public static function for(array $merge = [], array $except = [], ?Request $request = null): string
    {
        $request ??= request();
        $view = $merge['view'] ?? $request->query('view', 'board');

        $params = array_merge(
            $request->except(array_merge(['page'], $except)),
            $merge,
            ['view' => $view]
        );

        return route('admin.crm.leads.index', $params);
    }

    public static function toggle(string $key, mixed $value, ?Request $request = null): string
    {
        $request ??= request();

        if ((string) $request->query($key) === (string) $value) {
            return self::for([], [$key], $request);
        }

        return self::for([$key => $value], [$key], $request);
    }
}
