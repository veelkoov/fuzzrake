<?php

declare(strict_types=1);

namespace App\Tests\Photos\MiniatureUrlResolver;

use App\Photos\MiniaturesUpdateException;
use App\Photos\MiniatureUrlResolver\FurtrackMiniatureUrlResolver;
use App\Utils\DateTime\UtcClock;
use App\Utils\Web\FreeUrl;
use App\Utils\Web\HttpClient\HttpClientInterface;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Snapshots\SnapshotMetadata;
use App\Utils\Web\Url;
use PHPUnit\Framework\TestCase;
use Veelkoov\Debris\StringStringMap;

/**
 * @small
 */
class FurtrackMiniatureUrlResolverTest extends TestCase
{
    /**
     * @throws MiniaturesUpdateException
     */
    public function testSuccessfulResolve(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock
            ->expects(self::exactly(2))
            ->method('fetch')
            ->with(self::anything(), 'HEAD', self::anything(), null)
            ->willReturnCallback(function (Url $url, string $method, StringStringMap $headers, ?string $body) {
                self::assertSame(0, $headers->count());
                self::assertNull($body);

                return new Snapshot('', new SnapshotMetadata($url->getUrl(), UtcClock::now(), 200, [], []));
            });

        $subject = new FurtrackMiniatureUrlResolver($httpClientMock);

        self::assertSame(
            'https://orca2.furtrack.com/thumb/49767.jpg',
            $subject->getMiniatureUrl(new FreeUrl('https://www.furtrack.com/p/49767')),
        );
        self::assertSame(
            'https://orca2.furtrack.com/thumb/41933.jpg',
            $subject->getMiniatureUrl(new FreeUrl('https://www.furtrack.com/p/41933')),
        );
    }

    /**
     * @throws MiniaturesUpdateException
     */
    public function testNon200HttpResponse(): void
    {
        $httpClientMock = $this->createStub(HttpClientInterface::class);
        $httpClientMock->method('fetch')
            ->willReturn(new Snapshot('', new SnapshotMetadata('', UtcClock::now(), 403, [], [])));

        $subject = new FurtrackMiniatureUrlResolver($httpClientMock);

        self::expectException(MiniaturesUpdateException::class);
        self::expectExceptionMessage('Non-200 HTTP response code.');
        $subject->getMiniatureUrl(new FreeUrl('https://www.furtrack.com/p/49767'));
    }
}
