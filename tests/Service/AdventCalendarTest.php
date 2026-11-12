<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\AdventCalendar;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdventCalendarTest extends TestCase
{
    #[DataProvider('provideYears')]
    public function testItCountsTheDaysOfTheAdventCalendarOfAYear(int $year, int $expectedNumberOfDays): void
    {
        self::assertSame($expectedNumberOfDays, new AdventCalendar()->countDays($year));
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function provideYears(): iterable
    {
        yield 'first edition' => [2015, 25];
        yield 'last year with 25 days' => [2024, 25];
        yield 'first year with 12 days' => [2025, 12];
        yield 'after 2025' => [2026, 12];
    }
}
