<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * In the test environment, AOC_YEAR is 2024 and the leaderboards are configured in config/services_test.yaml:
 * "My friends" (leaderboard 1) and the "WA" group (leaderboards 2 and 3), answered by AocApiMockResponseFactory.
 */
final class DisplayLeaderboardsControllerTest extends WebTestCase
{
    public function testItDisplaysTheLeaderboardsOfTheConfiguredYearByDefault(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/leaderboards');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'Advent of Code 2024 Leaderboards');
        // 25 days before 2025, for each of the 2 leaderboards
        self::assertCount(2 * 25, $crawler->filter('th.day'));
    }

    public function testItDisplaysTheLeaderboardsOfTheRequestedYear(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/leaderboards/2025');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'Advent of Code 2025 Leaderboards');
        // 12 days since 2025, for each of the 2 leaderboards
        self::assertCount(2 * 12, $crawler->filter('th.day'));
    }

    public function testItDisplaysTheMembersOfEachLeaderboardSortedByStars(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/leaderboards/2025');

        self::assertSame(['My friends', 'WA'], $crawler->filter('h2')->each(static fn ($title): string => $title->text()));
        self::assertSame(
            ['Zoe', 'Alice', 'Carol'],
            $crawler->filter('.leaderboard')->eq(0)->filter('tbody tr td:first-child')->each(static fn ($cell): string => $cell->text()),
        );
        self::assertSame(
            ['Frank', 'Dave', 'Eve'],
            $crawler->filter('.leaderboard')->eq(1)->filter('tbody tr td:first-child')->each(static fn ($cell): string => $cell->text()),
        );
    }

    public function testItShowsAFullStarForDaysWithBothPartsCompleted(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/leaderboards/2025');

        // Carol: both parts of day 1, only part 1 of day 2
        $carolCells = $crawler->filter('.leaderboard')->eq(0)->filter('tbody tr')->eq(2)->filter('td');
        self::assertSame('Carol', $carolCells->eq(0)->text());
        self::assertStringContainsString('fullstar', $carolCells->eq(1)->attr('class'));
        self::assertSame('★', $carolCells->eq(2)->text());
        self::assertStringNotContainsString('fullstar', $carolCells->eq(2)->attr('class'));
        self::assertSame('', $carolCells->eq(3)->text());
        self::assertSame('3', $carolCells->last()->text());
    }

    public function testItRejectsAnInvalidYear(): void
    {
        $client = self::createClient();

        $client->request('GET', '/leaderboards/twenty');

        self::assertResponseStatusCodeSame(404);
    }
}
