<?php

declare(strict_types=1);

namespace App\Service;

final class AdventCalendar
{
    // Since 2025, Advent of Code lasts 12 days instead of 25
    private const int FIRST_YEAR_WITH_12_DAYS = 2025;

    public function countDays(int $year): int
    {
        if (self::FIRST_YEAR_WITH_12_DAYS > $year) {
            return 25;
        }

        return 12;
    }
}
