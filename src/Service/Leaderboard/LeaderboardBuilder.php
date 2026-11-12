<?php

declare(strict_types=1);

namespace App\Service\Leaderboard;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Builds the leaderboards to display from the ones configured in the LEADERBOARDS environment variable.
 *
 * @phpstan-type Member array{name: string, stars: int, local_score: int, completion_day_level: array<int, array<int, mixed>>}
 */
final readonly class LeaderboardBuilder
{
    /**
     * @param list<array{name: string, id: int, group?: string}> $leaderboards
     */
    public function __construct(
        private AocLeaderboardClient $aocLeaderboardClient,
        #[Autowire(env: 'json:LEADERBOARDS')]
        private array $leaderboards,
    ) {
    }

    /**
     * Leaderboards sharing the same "group" are merged and displayed under the group name.
     *
     * @return array<string, array<string, Member>> members sorted by stars then name, indexed by leaderboard (or group) name
     */
    public function buildLeaderboards(int $year): array
    {
        $membersByLeaderboard = [];

        foreach ($this->leaderboards as $leaderboard) {
            $displayedName = $leaderboard['group'] ?? $leaderboard['name'];
            $membersByLeaderboard[$displayedName] ??= [];

            $leaderboardData = $this->aocLeaderboardClient->fetchLeaderboard($year, $leaderboard['id']);
            foreach ($this->extractMembers($leaderboardData) as $memberName => $member) {
                // a member of several leaderboards of the same group is displayed only once
                $membersByLeaderboard[$displayedName][$memberName] = $member;
            }
        }

        foreach ($membersByLeaderboard as $displayedName => $members) {
            $membersByLeaderboard[$displayedName] = $this->sortByStarsThenName($members);
        }

        return $membersByLeaderboard;
    }

    /**
     * Anonymous members and members without any star are not displayed.
     *
     * @param array<string, mixed> $leaderboardData
     *
     * @return array<string, Member> indexed by member name
     */
    private function extractMembers(array $leaderboardData): array
    {
        $members = [];

        foreach ($leaderboardData['members'] as $member) {
            if (null === $member['name'] || '' === $member['name'] || 0 === $member['stars']) {
                continue;
            }

            $members[$member['name']] = [
                'name' => $member['name'],
                'stars' => $member['stars'],
                'local_score' => $member['local_score'],
                'completion_day_level' => $member['completion_day_level'],
            ];
        }

        return $members;
    }

    /**
     * @param array<string, Member> $members
     *
     * @return array<string, Member>
     */
    private function sortByStarsThenName(array $members): array
    {
        uasort($members, static function (array $firstMember, array $secondMember): int {
            return $secondMember['stars'] <=> $firstMember['stars']
                ?: $firstMember['name'] <=> $secondMember['name'];
        });

        return $members;
    }
}
