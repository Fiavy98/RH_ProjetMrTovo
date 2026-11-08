<?php

namespace App\Services\Bloc7;

final class AbsenceRules
{
    public static function periodsOverlap(string $firstStart, string $firstEnd, string $secondStart, string $secondEnd): bool
    {
        return $firstStart <= $secondEnd && $firstEnd >= $secondStart;
    }
}
