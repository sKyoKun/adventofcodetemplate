<?php

declare(strict_types=1);

namespace App\Service\Leaderboard;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LeaderboardService
{
    private const AOC_API_URL = 'https://adventofcode.com/__YEAR__/leaderboard/private/view/__LEADERBOARDID__.json';

    public function __construct(
        #[Autowire(env: 'file:AUTH_FILE')]
        private readonly string $cookie,
        #[Autowire(env: 'json:LEADERBOARDS')]
        private readonly array $leaderboards,
    ) {
    }

    public function retrieveLeaderboards(int $year): array
    {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => 'Cookie: session='.$this->cookie."\r\nUser-Agent: github.com/sKyoKun\r\n",
            ],
        ];

        $context = stream_context_create($options);

        // Open the file using the HTTP headers set above
        $json = [];
        foreach ($this->leaderboards as $leaderboard) {
            $url = str_replace('__YEAR__', ''.$year, self::AOC_API_URL);
            $url = str_replace('__LEADERBOARDID__', ''.$leaderboard['id'], $url);
            $json[$leaderboard['name']] = file_get_contents($url, false, $context);
        }

        return $json;
    }

    public function parseJsonLeaderboard($json): array
    {
        $leaderboard = [];
        $decodedJson = json_decode($json);

        foreach ($decodedJson->members as $member) {
            if (empty($member->name) || 0 === $member->stars) {
                continue;
            }
            $leaderboard[$member->name]['stars'] = $member->stars;
            $leaderboard[$member->name]['name'] = $member->name;
            $leaderboard[$member->name]['local_score'] = $member->local_score;
            $leaderboard[$member->name]['completion_day_level'] = [];
            foreach ($member->completion_day_level as $key => $completionDay) {
                $leaderboard[$member->name]['completion_day_level'][$key] = $completionDay;
            }
        }

        // sort by stars then by name
        $this->orderLeaderboard($leaderboard);

        return $leaderboard;
    }

    public function orderLeaderboard(&$leaderboard): void
    {
        uasort($leaderboard, function ($a, $b) {
            return $b['stars'] <=> $a['stars']
                ?: $a['name'] <=> $b['name'];
        });
    }
}
