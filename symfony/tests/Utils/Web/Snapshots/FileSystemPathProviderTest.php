<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\Snapshots;

use App\Utils\Web\Snapshots\FileSystemPathProvider;
use App\Utils\Web\Url\FreeUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class FileSystemPathProviderTest extends TestCase
{
    /**
     * @return list<array{string, non-empty-string}>
     */
    public static function getSnapshotDirPathDataProvider(): array
    {
        return [
            ['https://www.tumblr.com/getfursuit', 'T/tumblr.com/getfursuit'],
            ['https://furries.club/@getfursuit', 'F/furries.club/getfursuit'],
            ['https://github.com/veelkoov/fuzzrake/pull/194', 'G/github.com/veelkoov_fuzzrake_pull_194'],
            ['https://getfursu.it/data_updates.html#anchor', 'G/getfursu.it/data_updates.html'],
        ];
    }

    /**
     * @param non-empty-string $expectedPrefix
     */
    #[DataProvider('getSnapshotDirPathDataProvider')]
    public function testGetSnapshotDirPath(string $inputUrl, string $expectedPrefix): void
    {
        $subject = new FileSystemPathProvider();

        self::assertStringStartsWith($expectedPrefix, $subject->getSnapshotDirPath(new FreeUrl($inputUrl, '')));
    }
}
