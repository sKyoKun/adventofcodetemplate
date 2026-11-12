<?php

declare(strict_types=1);

namespace App\Tests\HttpClient;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Fake Advent of Code API: answers private leaderboard requests with the JSON files of tests/Fixtures/AocApi.
 */
final class AocApiMockResponseFactory
{
    private const string LEADERBOARD_URL_PATTERN = '#^https://adventofcode\.com/\d{4}/leaderboard/private/view/(\d+)\.json$#';

    /**
     * @param array<string, mixed> $options
     */
    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        if (1 !== preg_match(self::LEADERBOARD_URL_PATTERN, $url, $matches)) {
            return new MockResponse('', ['http_code' => 404]);
        }

        $fixturePath = \dirname(__DIR__).'/Fixtures/AocApi/leaderboard_'.$matches[1].'.json';
        if (false === is_file($fixturePath)) {
            return new MockResponse('', ['http_code' => 404]);
        }

        return new MockResponse(file_get_contents($fixturePath), [
            'http_code' => 200,
            'response_headers' => ['Content-Type' => 'application/json'],
        ]);
    }
}
