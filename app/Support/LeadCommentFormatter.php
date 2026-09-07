<?php

namespace App\Support;

use Illuminate\Support\Collection;

class LeadCommentFormatter
{
    /**
     * Render stored comment text with @mention highlights.
     *
     * @param  Collection<int, \App\Models\Admin>  $admins
     */
    public static function body(string $note, Collection $admins): string
    {
        $escaped = e($note);

        foreach ($admins->sortByDesc(fn ($admin) => mb_strlen($admin->name)) as $admin) {
            $name = preg_quote($admin->name, '/');
            $escaped = preg_replace(
                '/@'.$name.'(?=\s|$|[.,!?;:])/u',
                '<span class="crm-mention" data-mention-id="'.(int) $admin->id.'">@'.e($admin->name).'</span>',
                $escaped
            );
        }

        return nl2br($escaped, false);
    }
}
