<?php

namespace App\Support;

use Illuminate\Http\Request;

final class CrmLeadFilterUrl
{
    /**
     * Ephemeral UI query params that must never stick on filter/navigation links.
     * (e.g. after create we redirect with open_lead — those links would re-open the panel forever.)
     *
     * @var list<string>
     */
    public const EPHEMERAL_QUERY = ['open_lead', 'open_create'];

    /** @param  array<string, mixed>  $merge
     * @param  list<string>  $except
     */
    public static function for(array $merge = [], array $except = [], ?Request $request = null): string
    {
        $request ??= request();
        $view = $merge['view'] ?? $request->query('view', 'board');

        $params = array_merge(
            $request->except(array_merge(['page'], self::EPHEMERAL_QUERY, $except)),
            $merge,
            ['view' => $view]
        );

        unset($params['open_lead'], $params['open_create']);

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
