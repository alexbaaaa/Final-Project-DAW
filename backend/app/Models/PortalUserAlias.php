<?php

namespace App\Models;

use Illuminate\Support\Str;

class PortalUserAlias
{
    public static function makeUnique(string $firstName, string $lastName, ?string $birthDate = null, ?int $ignoreUserId = null): string
    {
        $nameInitial = Str::substr(self::normalizePart($firstName), 0, 1) ?: 'u';
        $surnameParts = preg_split('/\s+/', trim($lastName)) ?: [];
        $firstSurname = self::normalizePart($surnameParts[0] ?? 'user');
        $secondSurname = self::normalizePart($surnameParts[1] ?? '');
        $yearSuffix = $birthDate ? substr((string) date('Y', strtotime($birthDate)), -2) : '00';
        $firstAlias = $nameInitial . $firstSurname . $yearSuffix;
        $secondAlias = $secondSurname !== '' ? $nameInitial . $secondSurname . $yearSuffix : $firstAlias;

        foreach ([$firstAlias, $secondAlias] as $candidate) {
            if (!self::exists($candidate, $ignoreUserId)) {
                return $candidate;
            }
        }

        $index = 2;

        do {
            $candidate = $secondAlias . $index;
            $index++;
        } while (self::exists($candidate, $ignoreUserId));

        return $candidate;
    }

    private static function exists(string $alias, ?int $ignoreUserId = null): bool
    {
        return PortalUser::query()
            ->where('alias', $alias)
            ->when($ignoreUserId !== null, fn ($query) => $query->where('id', '!=', $ignoreUserId))
            ->exists();
    }

    private static function normalizePart(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($value))) ?: '';
    }
}
