<?php

declare(strict_types=1);

namespace App\Tests\Service\Leaderboard;

use App\Service\Leaderboard\AocLeaderboardClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class AocLeaderboardClientTest extends TestCase
{
    private const string LEADERBOARD_JSON = '{"event": "2025", "owner_id": 1, "members": {}}';

    private MockClock $clock;

    protected function setUp(): void
    {
        $this->clock = new MockClock();
    }

    public function testItFetchesThePrivateLeaderboardOfTheYearWithTheSessionCookie(): void
    {
        $mockResponse = new MockResponse(self::LEADERBOARD_JSON, ['http_code' => 200]);
        $aocLeaderboardClient = $this->createAocLeaderboardClient(new MockHttpClient($mockResponse));

        $leaderboardData = $aocLeaderboardClient->fetchLeaderboard(2025, 12345);

        self::assertSame(['event' => '2025', 'owner_id' => 1, 'members' => []], $leaderboardData);
        self::assertSame('GET', $mockResponse->getRequestMethod());
        self::assertSame('https://adventofcode.com/2025/leaderboard/private/view/12345.json', $mockResponse->getRequestUrl());
        self::assertContains('Cookie: session=my-session-cookie', $mockResponse->getRequestOptions()['headers']);
        self::assertContains('User-Agent: github.com/sKyoKun', $mockResponse->getRequestOptions()['headers']);
    }

    public function testItDoesNotCallTheApiAgainBefore30Minutes(): void
    {
        $httpClient = $this->createHttpClientAlwaysAnswering(self::LEADERBOARD_JSON);
        $aocLeaderboardClient = $this->createAocLeaderboardClient($httpClient);

        $aocLeaderboardClient->fetchLeaderboard(2025, 12345);
        $this->clock->sleep(30 * 60 - 1);
        $leaderboardData = $aocLeaderboardClient->fetchLeaderboard(2025, 12345);

        self::assertSame(1, $httpClient->getRequestsCount());
        self::assertSame(['event' => '2025', 'owner_id' => 1, 'members' => []], $leaderboardData);
    }

    public function testItCallsTheApiAgainAfter30Minutes(): void
    {
        $httpClient = $this->createHttpClientAlwaysAnswering(self::LEADERBOARD_JSON);
        $aocLeaderboardClient = $this->createAocLeaderboardClient($httpClient);

        $aocLeaderboardClient->fetchLeaderboard(2025, 12345);
        $this->clock->sleep(30 * 60 + 1);
        $aocLeaderboardClient->fetchLeaderboard(2025, 12345);

        self::assertSame(2, $httpClient->getRequestsCount());
    }

    public function testItCachesEachLeaderboardOfEachYearSeparately(): void
    {
        $httpClient = $this->createHttpClientAlwaysAnswering(self::LEADERBOARD_JSON);
        $aocLeaderboardClient = $this->createAocLeaderboardClient($httpClient);

        $aocLeaderboardClient->fetchLeaderboard(2025, 12345);
        $aocLeaderboardClient->fetchLeaderboard(2025, 67890);
        $aocLeaderboardClient->fetchLeaderboard(2024, 12345);

        self::assertSame(3, $httpClient->getRequestsCount());
    }

    public function testItDoesNotCacheAFailure(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
            new MockResponse(self::LEADERBOARD_JSON, ['http_code' => 200]),
        ]);
        $aocLeaderboardClient = $this->createAocLeaderboardClient($httpClient);

        try {
            $aocLeaderboardClient->fetchLeaderboard(2025, 12345);
            self::fail('The first call should have failed.');
        } catch (\RuntimeException) {
        }
        $leaderboardData = $aocLeaderboardClient->fetchLeaderboard(2025, 12345);

        self::assertSame(2, $httpClient->getRequestsCount());
        self::assertSame(['event' => '2025', 'owner_id' => 1, 'members' => []], $leaderboardData);
    }

    #[DataProvider('provideUnusableResponses')]
    public function testItThrowsAClearErrorWhenTheLeaderboardCannotBeRetrieved(MockResponse $mockResponse): void
    {
        $aocLeaderboardClient = $this->createAocLeaderboardClient(new MockHttpClient($mockResponse));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The leaderboard 12345 of 2025 cannot be retrieved');

        $aocLeaderboardClient->fetchLeaderboard(2025, 12345);
    }

    /**
     * @return iterable<string, array{MockResponse}>
     */
    public static function provideUnusableResponses(): iterable
    {
        yield 'expired session cookie, redirected to the login page' => [
            new MockResponse('', ['http_code' => 302, 'response_headers' => ['Location' => 'https://adventofcode.com/2025/auth/login']]),
        ];
        yield 'leaderboard not found' => [new MockResponse('', ['http_code' => 404])];
        yield 'HTML page instead of JSON' => [new MockResponse('<html><body>Log in</body></html>', ['http_code' => 200])];
    }

    private function createAocLeaderboardClient(MockHttpClient $httpClient): AocLeaderboardClient
    {
        return new AocLeaderboardClient($httpClient, new ArrayAdapter(clock: $this->clock), 'my-session-cookie');
    }

    private function createHttpClientAlwaysAnswering(string $json): MockHttpClient
    {
        return new MockHttpClient(static function () use ($json): MockResponse {
            return new MockResponse($json, ['http_code' => 200]);
        });
    }
}
