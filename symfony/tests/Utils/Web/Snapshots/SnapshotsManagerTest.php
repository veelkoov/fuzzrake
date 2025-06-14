<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\Snapshots;

use App\Tests\TestUtils\Cases\Traits\FilesystemTrait;
use App\Tests\TestUtils\Http\ExpectedHttpCall;
use App\Tests\TestUtils\Http\HttpClientMockTrait;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Snapshots\SnapshotsManager;
use App\Utils\Web\Snapshots\SnapshotsSerializer;
use App\Utils\Web\Url\FreeUrl;
use PHPUnit\Framework\TestCase;
use Veelkoov\Debris\Base\DStringMap;

class SnapshotsManagerTest extends TestCase
{
    use FilesystemTrait;
    use HttpClientMockTrait;

    public function testSnapshotsBeingSavedInTheRightDirectory(): void
    {
        $httpClientMock = self::getHttpClientMock(
            new ExpectedHttpCall('GET', 'https://getfursu.it/'),
        );

        $serializerMock = self::createMock(SnapshotsSerializer::class);
        $serializerMock->expects(self::once())->method('save')
            ->willReturnCallback(function (string $snapshotDirPath, Snapshot $_): void {
                self::assertStringStartsWith($this->testsTempDir, $snapshotDirPath);
            });
        $serializerMock->expects(self::once())->method('load')->willReturn(null);

        $subject = new SnapshotsManager(
            $serializerMock,
            $httpClientMock,
            $this->testsTempDir,
        );

        $subject->get(new FreeUrl('https://getfursu.it/'), false);
    }

    public function testCachingAndRefetching(): void
    {
        $httpClientMock = self::getHttpClientMock(
            new ExpectedHttpCall('GET', 'https://getfursu.it/'),
            new ExpectedHttpCall('GET', 'https://getfursu.it/info', responseBody: 'first'),
            new ExpectedHttpCall('GET', 'https://getfursu.it/info', responseBody: 'second'),
        );

        /** @var DStringMap<Snapshot> $cache */
        $cache = new DStringMap();

        $serializerMock = self::createMock(SnapshotsSerializer::class);
        $serializerMock->expects(self::exactly(3))->method('save')
            ->willReturnCallback($cache->set(...));
        $serializerMock->expects(self::exactly(4))->method('load')
            ->willReturnCallback(function (string $snapshotDirPath) use ($cache): ?Snapshot {
                return $cache->getOrDefault($snapshotDirPath, static fn () => null);
            });

        $subject = new SnapshotsManager(
            $serializerMock,
            $httpClientMock,
            $this->testsTempDir,
        );

        $rootSnapshot1 = $subject->get(new FreeUrl('https://getfursu.it/'), false);
        $infoSnapshot1 = $subject->get(new FreeUrl('https://getfursu.it/info'), false);
        $rootSnapshot2 = $subject->get(new FreeUrl('https://getfursu.it/'), false);
        $infoSnapshot2 = $subject->get(new FreeUrl('https://getfursu.it/info'), false);
        $infoSnapshot3 = $subject->get(new FreeUrl('https://getfursu.it/info'), true);

        self::assertSame($rootSnapshot1, $rootSnapshot2);
        self::assertSame($infoSnapshot1, $infoSnapshot2);
        self::assertNotSame($infoSnapshot1, $infoSnapshot3);
        self::assertSame('first', $infoSnapshot1->contents);
        self::assertSame('second', $infoSnapshot3->contents);
    }
}
