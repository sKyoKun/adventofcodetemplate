<?php

declare(strict_types=1);

namespace App\Service\Leaderboard;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
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

    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire(env: 'trim:file:AUTH_FILE')]
        private string $sessionCookie,
    ) {
    }

    /**
     * @return array<string, mixed> the decoded JSON of the private leaderboard
     */
    public function fetchLeaderboard(int $year, int $leaderboardId): array
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
