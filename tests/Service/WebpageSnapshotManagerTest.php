<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\WebpageSnapshotManager;
use App\Tests\TestUtils\Cases\TestCase;
use App\Tracking\Web\HttpClient\GentleHttpClient;
use App\Tracking\Web\Url\Fetchable;
use App\Tracking\Web\WebpageSnapshot\Cache;
use App\Utils\DateTime\UtcClock;
use App\Utils\TestUtils\UtcClockMock;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @small
 */
class WebpageSnapshotManagerTest extends TestCase
{
    public function testGetCreatesProperSnapshot(): void
    {
        UtcClockMock::start();

        $contents = 'some-testing-contents';
        $url = 'some-testing-url';
        $statusCode = 482;
        $headers = ['some-testing-header' => ['some-testing-header-value']];
        $ownerName = 'some-testing-owner-name';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn($contents);
        $response->method('getStatusCode')->willReturn(482);
        $response->method('getHeaders')->willReturn($headers);

        $httpClient = $this->createMock(GentleHttpClient::class);
        $httpClient->method('get')->with($url)->willReturn($response);
        $cache = $this->createMock(Cache::class);
        $logger = $this->createMock(LoggerInterface::class);

        $fetchable = $this->createMock(Fetchable::class);
        $fetchable->method('getUrl')->willReturn($url);
        $fetchable->method('getOwnerName')->willReturn($ownerName);

        $subject = new WebpageSnapshotManager($httpClient, $cache, $logger);
        $actual = $subject->get($fetchable, true);

        self::assertEquals($contents, $actual->contents);
        self::assertEquals($url, $actual->url);
        self::assertEquals($statusCode, $actual->httpCode);
        self::assertEquals($headers, $actual->headers);
        self::assertEquals($ownerName, $actual->ownerName);
        self::assertEquals(UtcClock::now(), $actual->retrievedAt);
        self::assertEquals([], $actual->errors);
    }
}
