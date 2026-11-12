<?php

declare(strict_types=1);

namespace App\Service\Leaderboard;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Retrieves private leaderboards from the Advent of Code API.
 */
final readonly class AocLeaderboardClient
{
    private const string LEADERBOARD_URL = 'https://adventofcode.com/%d/leaderboard/private/view/%d.json';

    // Advent of Code asks automated tools to identify themselves with a way to contact their author
    private const string USER_AGENT = 'github.com/sKyoKun';

    // Advent of Code asks not to request a private leaderboard more than once every 15 minutes
    private const int CACHE_LIFETIME_IN_SECONDS = 30 * 60;

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        #[Autowire(env: 'trim:file:AUTH_FILE')]
        private string $sessionCookie,
    ) {
    }

    /**
     * The leaderboard is kept in cache for 30 minutes, failures are not cached.
     *
     * @return array<string, mixed> the decoded JSON of the private leaderboard
     */
    public function fetchLeaderboard(int $year, int $leaderboardId): array
    {
        $cacheKey = \sprintf('aoc_leaderboard_%d_%d', $year, $leaderboardId);

        return $this->cache->get($cacheKey, function (ItemInterface $cacheItem) use ($year, $leaderboardId): array {
            $cacheItem->expiresAfter(self::CACHE_LIFETIME_IN_SECONDS);

            return $this->requestLeaderboard($year, $leaderboardId);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function requestLeaderboard(int $year, int $leaderboardId): array
    {
        $response = $this->httpClient->request('GET', \sprintf(self::LEADERBOARD_URL, $year, $leaderboardId), [
            'headers' => [
                'Cookie' => 'session='.$this->sessionCookie,
                'User-Agent' => self::USER_AGENT,
            ],
            // with an expired session cookie, Advent of Code redirects to its login page instead of returning JSON
            'max_redirects' => 0,
        ]);

        try {
            return $response->toArray();
        } catch (ExceptionInterface $exception) {
            throw new \RuntimeException(\sprintf('The leaderboard %d of %d cannot be retrieved: check that you are a member of it and that the session cookie in cookie.txt is still valid.', $leaderboardId, $year), previous: $exception);
        }
    }
}
