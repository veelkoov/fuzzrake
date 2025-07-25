<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Entity\Creator as CreatorE;
use App\Tracking\Data\AnalysisInput;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Snapshots\SnapshotMetadata;
use App\Utils\Web\Url\FreeUrl;

trait MocksTrait
{
    protected function getPersistedCreatorMock(): Creator
    {
        $result = $this->getMockBuilder(CreatorE::class)->onlyMethods(['getId'])->getMock();
        $result->method('getId')->willReturn(1);

        return Creator::wrap($result);
    }

    /**
     * @param list<string> $formerly
     */
    protected static function getAnalysisInput(
        string $creatorId = 'TEST001',
        string $name = '',
        array $formerly = [],
        string $contents = '',
        string $url = 'https://example.com/',
    ): AnalysisInput {
        $urlObj = new FreeUrl($url, $creatorId);
        $snapshot = self::getSnapshot($contents, $url, $creatorId);
        $creator = new Creator()->setCreatorId($creatorId)->setName($name)->setFormerly($formerly);

        return new AnalysisInput($urlObj, $snapshot, $creator);
    }

    protected static function getSnapshot(
        string $contents = '',
        string $url = 'https://example.com/',
        string $creatorId = 'TEST001',
    ): Snapshot {
        return new Snapshot($contents, new SnapshotMetadata($url, $creatorId, UtcClock::now(), 200, [], []));
    }
}
