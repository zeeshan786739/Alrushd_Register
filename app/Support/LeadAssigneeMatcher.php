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

    /**
     * Match a teammate when the lead email local-part starts with their name or email prefix.
     * Example: foysol11@gmail.com → teammate "Foysol Ahmed".
     *
     * @param  Builder<Admin>  $query
     */
    public function matchByLeadEmail(Builder $query, string $email): ?Admin
    {
        $email = trim(mb_strtolower($email));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        [$localPart] = explode('@', $email, 2);
        $localPart = trim($localPart);
        if ($localPart === '') {
            return null;
        }

        $exactMember = (clone $query)->whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
        if ($exactMember instanceof Admin) {
            return $exactMember;
        }

        $matches = collect();

        foreach ((clone $query)->get() as $admin) {
            if ($this->localPartMatchesAdmin($localPart, $admin)) {
                $matches->push($admin);
            }
        }

        return $matches->unique('id')->count() === 1 ? $matches->first() : null;
    }

    private function localPartMatchesAdmin(string $localPart, Admin $admin): bool
    {
        $adminEmailLocal = mb_strtolower(trim(explode('@', trim((string) $admin->email))[0] ?? ''));
        if ($adminEmailLocal !== '' && strlen($adminEmailLocal) >= 3 && str_starts_with($localPart, $adminEmailLocal)) {
            return true;
        }

        $firstName = mb_strtolower(trim(explode(' ', trim($admin->name))[0] ?? ''));
        if ($firstName === '' || strlen($firstName) < 3) {
            return false;
        }

        if (str_starts_with($localPart, $firstName)) {
            return true;
        }

        $namePrefix = substr($localPart, 0, strlen($firstName));
        similar_text($namePrefix, $firstName, $percent);

        return $percent >= 80.0 || levenshtein($namePrefix, $firstName) <= 2;
    }
}
