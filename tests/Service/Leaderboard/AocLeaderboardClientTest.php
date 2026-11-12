<?php

declare(strict_types=1);

namespace App\Tests\Service\Leaderboard;

use App\Service\Leaderboard\AocLeaderboardClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class AocLeaderboardClientTest extends TestCase
{
    public function testItFetchesThePrivateLeaderboardOfTheYearWithTheSessionCookie(): void
    {
        $mockResponse = new MockResponse('{"event": "2025", "owner_id": 1, "members": {}}', ['http_code' => 200]);
        $aocLeaderboardClient = new AocLeaderboardClient(new MockHttpClient($mockResponse), 'my-session-cookie');

        $leaderboardData = $aocLeaderboardClient->fetchLeaderboard(2025, 12345);

        self::assertSame(['event' => '2025', 'owner_id' => 1, 'members' => []], $leaderboardData);
        self::assertSame('GET', $mockResponse->getRequestMethod());
        self::assertSame('https://adventofcode.com/2025/leaderboard/private/view/12345.json', $mockResponse->getRequestUrl());
        self::assertContains('Cookie: session=my-session-cookie', $mockResponse->getRequestOptions()['headers']);
        self::assertContains('User-Agent: github.com/sKyoKun', $mockResponse->getRequestOptions()['headers']);
    }

    #[DataProvider('provideUnusableResponses')]
    public function testItThrowsAClearErrorWhenTheLeaderboardCannotBeRetrieved(MockResponse $mockResponse): void
    {
        $aocLeaderboardClient = new AocLeaderboardClient(new MockHttpClient($mockResponse), 'expired-session-cookie');

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
}
