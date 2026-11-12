<?php

declare(strict_types=1);

namespace App\Tests\Service\Leaderboard;

use App\Service\Leaderboard\AocLeaderboardClient;
use App\Service\Leaderboard\LeaderboardBuilder;
use App\Tests\HttpClient\AocApiMockResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;

/**
 * Leaderboards 1, 2 and 3 are the JSON files of tests/Fixtures/AocApi.
 */
final class LeaderboardBuilderTest extends TestCase
{
    public function testItIgnoresAnonymousMembersAndMembersWithoutStars(): void
    {
        $leaderboardBuilder = $this->createLeaderboardBuilder([['name' => 'My friends', 'id' => 1]]);

        $leaderboards = $leaderboardBuilder->buildLeaderboards(2025);

        self::assertSame(['Zoe', 'Alice', 'Carol'], array_keys($leaderboards['My friends']));
    }

    public function testItSortsMembersByStarsThenByName(): void
    {
        $leaderboardBuilder = $this->createLeaderboardBuilder([['name' => 'My friends', 'id' => 1]]);

        $members = $leaderboardBuilder->buildLeaderboards(2025)['My friends'];

        // Zoe has 4 stars, Alice and Carol both have 3 stars
        self::assertSame(['Zoe', 'Alice', 'Carol'], array_column($members, 'name'));
    }

    public function testItKeepsTheDataDisplayedForEachMember(): void
    {
        $leaderboardBuilder = $this->createLeaderboardBuilder([['name' => 'My friends', 'id' => 1]]);

        $carol = $leaderboardBuilder->buildLeaderboards(2025)['My friends']['Carol'];

        self::assertSame('Carol', $carol['name']);
        self::assertSame(3, $carol['stars']);
        self::assertSame(12, $carol['local_score']);
        // both parts of day 1, only the first part of day 2
        self::assertSame([1, 2], array_keys($carol['completion_day_level'][1]));
        self::assertSame([1], array_keys($carol['completion_day_level'][2]));
    }

    public function testItMergesTheLeaderboardsOfTheSameGroupUnderTheGroupName(): void
    {
        $leaderboardBuilder = $this->createLeaderboardBuilder([
            ['name' => 'WA Lyon', 'id' => 2, 'group' => 'WA'],
            ['name' => 'WA Paris', 'id' => 3, 'group' => 'WA'],
        ]);

        $leaderboards = $leaderboardBuilder->buildLeaderboards(2025);

        self::assertSame(['WA'], array_keys($leaderboards));
        // Dave is a member of both leaderboards but is displayed once
        self::assertSame(['Frank', 'Dave', 'Eve'], array_keys($leaderboards['WA']));
    }

    public function testItDisplaysLeaderboardsWithoutGroupSeparatelyInTheConfiguredOrder(): void
    {
        $leaderboardBuilder = $this->createLeaderboardBuilder([
            ['name' => 'WA Lyon', 'id' => 2, 'group' => 'WA'],
            ['name' => 'My friends', 'id' => 1],
            ['name' => 'WA Paris', 'id' => 3, 'group' => 'WA'],
        ]);

        $leaderboards = $leaderboardBuilder->buildLeaderboards(2025);

        self::assertSame(['WA', 'My friends'], array_keys($leaderboards));
    }

    public function testItBuildsNoLeaderboardWhenNoneIsConfigured(): void
    {
        $leaderboardBuilder = $this->createLeaderboardBuilder([]);

        self::assertSame([], $leaderboardBuilder->buildLeaderboards(2025));
    }

    /**
     * @param list<array{name: string, id: int, group?: string}> $leaderboards
     */
    private function createLeaderboardBuilder(array $leaderboards): LeaderboardBuilder
    {
        $aocLeaderboardClient = new AocLeaderboardClient(
            new MockHttpClient(new AocApiMockResponseFactory()),
            new ArrayAdapter(),
            'test-session-cookie',
        );

        return new LeaderboardBuilder($aocLeaderboardClient, $leaderboards);
    }
}
