<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\HttpClient;

use App\Tests\TestUtils\Http\ExpectedHttpCall;
use App\Tests\TestUtils\Http\HttpClientMockTrait;
use App\Utils\DateTime\UtcClock;
use App\Utils\Web\HttpClient\GentleHttpClient;
use App\Utils\Web\Url\FreeUrl;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

#[Small]
class GentleHttpClientTest extends TestCase
{
    use ClockSensitiveTrait;
    use HttpClientMockTrait;

    public function testFollowingRequestsToTheSameHostAreDelayed(): void
    {
        self::mockTime();

        $subject = new GentleHttpClient($this->getHttpClientMock(
            new ExpectedHttpCall('GET', 'https://getfursu.it/'),
            new ExpectedHttpCall('GET', 'http://getfursu.it/info'),
        ));

        $now = UtcClock::time();

        $subject->fetch(new FreeUrl('https://getfursu.it/'));
        self::assertSame($now, UtcClock::time(), 'First call should be immediate.');

        $subject->fetch(new FreeUrl('http://getfursu.it/info'));
        self::assertSame($now + 5, UtcClock::time(), 'Second call should happen after 5 seconds.');
    }
}
