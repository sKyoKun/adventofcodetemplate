<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Day1Service;
use PHPUnit\Framework\TestCase;

final class Day1ServiceTest extends TestCase
{
    private Day1Service $day1Service;

    protected function setUp(): void
    {
        $this->day1Service = new Day1Service();
    }

    // Replace this placeholder with real tests, e.g. one behaviour per test and a data provider for the variants:
    // #[DataProvider('provideExampleLines')]
    // public function testItComputesTheExpectedValue(string $line, int $expectedValue): void
    public function testDay1ServiceIsTested(): void
    {
        self::markTestIncomplete('Write the tests of the day 1 service.');
    }
}
