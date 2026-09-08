<?php

namespace App\Support;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder;

final class LeadAssigneeMatcher
{
    /**
     * @param  Builder<Admin>  $query
     */
    public function match(Builder $query, string $name): ?Admin
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
        if ($name === '') {
            return null;
        }

        $lower = mb_strtolower($name);

        $exact = (clone $query)->whereRaw('LOWER(TRIM(name)) = ?', [$lower])->first();
        if ($exact instanceof Admin) {
            return $exact;
        }

        $firstToken = mb_strtolower(explode(' ', $name)[0]);
        $firstNameMatches = (clone $query)->get()->filter(function (Admin $admin) use ($firstToken): bool {
            $adminFirst = mb_strtolower(explode(' ', trim($admin->name))[0] ?? '');

            return $adminFirst !== '' && $adminFirst === $firstToken;
        });
        if ($firstNameMatches->count() === 1) {
            return $firstNameMatches->first();
        }

        $fuzzyFirstMatches = (clone $query)->get()->filter(function (Admin $admin) use ($firstToken): bool {
            $adminFirst = mb_strtolower(explode(' ', trim($admin->name))[0] ?? '');
            if ($adminFirst === '') {
                return false;
            }
            similar_text($firstToken, $adminFirst, $percent);

            return $percent >= 80.0 || levenshtein($firstToken, $adminFirst) <= 2;
        });
        if ($fuzzyFirstMatches->count() === 1) {
            return $fuzzyFirstMatches->first();
        }

        $best = null;
        $bestScore = 0.0;
        foreach ((clone $query)->get() as $admin) {
            similar_text($lower, mb_strtolower(trim($admin->name)), $percent);
            if ($percent > $bestScore) {
                $bestScore = $percent;
                $best = $admin;
            }
        }

        return $bestScore >= 72.0 ? $best : null;
    }
}
