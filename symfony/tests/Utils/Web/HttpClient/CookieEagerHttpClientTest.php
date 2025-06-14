<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\HttpClient;

use App\Tests\TestUtils\Http\ExpectedHttpCall;
use App\Tests\TestUtils\Http\HttpClientMockTrait;
use App\Utils\Web\HttpClient\CookieEagerHttpClient;
use App\Utils\Web\Url\FreeUrl;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class CookieEagerHttpClientTest extends TestCase
{
    use HttpClientMockTrait;

    public function testNotCookieEagerCase(): void
    {
        $subject = new CookieEagerHttpClient(self::getHttpClientMock(
            new ExpectedHttpCall('GET', 'https://www.instagram.com/getfursu.it/'),
            new ExpectedHttpCall('GET', 'https://www.instagram.com/finland/'),
        ));

        $subject->fetch(new FreeUrl('https://www.instagram.com/getfursu.it/'));
        $subject->fetch(new FreeUrl('https://www.instagram.com/finland/'));
    }

    public function testCookieEagerCase(): void
    {
        $subject = new CookieEagerHttpClient(self::getHttpClientMock(
            new ExpectedHttpCall('GET', 'https://x.com/'),
            new ExpectedHttpCall('GET', 'https://twitter.com/getfursuit'),
            new ExpectedHttpCall('GET', 'https://twitter.com/veelkoov'),
        ));

        $subject->fetch(new FreeUrl('https://twitter.com/getfursuit'));
        $subject->fetch(new FreeUrl('https://twitter.com/veelkoov'));
    }
}
