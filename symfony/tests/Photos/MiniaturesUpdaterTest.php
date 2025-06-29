<?php

declare(strict_types=1);

namespace App\Tests\Photos;

use App\Photos\MiniaturesUpdater;
use App\Photos\MiniatureUrlResolver\FurtrackMiniatureUrlResolver;
use App\Photos\MiniatureUrlResolver\ScritchMiniatureUrlResolver;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Web\Url\Url;
use PHPUnit\Framework\Attributes\Small;
use Psr\Log\LoggerInterface;

#[Small]
class MiniaturesUpdaterTest extends FuzzrakeTestCase
{
    private MiniaturesUpdater $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $loggerMock = $this->createMock(LoggerInterface::class);

        $furtrackResolverMock = $this->createMock(FurtrackMiniatureUrlResolver::class);
        $furtrackResolverMock->method('supports')->willReturnCallback(
            static fn (string $url) => str_starts_with($url, 'furtrack_photo_'));
        $furtrackResolverMock->method('getMiniatureUrl')->willReturnCallback(
            static fn (Url $url) => str_replace('furtrack_photo_', 'furtrack_miniature_', $url->getUrl()));

        $scritchResolverMock = $this->createMock(ScritchMiniatureUrlResolver::class);
        $scritchResolverMock->method('supports')->willReturnCallback(
            static fn (string $url) => str_starts_with($url, 'scritch_photo_'));
        $scritchResolverMock->method('getMiniatureUrl')->willReturnCallback(
            static fn (Url $url) => str_replace('scritch_photo_', 'scritch_miniature_', $url->getUrl()));

        $this->subject = new MiniaturesUpdater($loggerMock, $furtrackResolverMock, $scritchResolverMock);
    }

    public function testWhenPhotosCountEqualsMiniatureCountCreatorIsUnchangedWithoutForce(): void
    {
        $creator = (new Creator())
            ->setCreatorId('CREATOR')
            ->setPhotoUrls(['scritch_photo_1', 'scritch_photo_2'])
            ->setMiniatureUrls(['scritch_wrongMiniature_1', 'scritch_wrongMiniature_2']);

        $this->subject->updateCreatorMiniaturesFor($creator, false);

        self::assertSame(['scritch_wrongMiniature_1', 'scritch_wrongMiniature_2'], $creator->getMiniatureUrls());
    }

    public function testWhenPhotosCountEqualsMiniatureCountCreatorIsUpdatedWithForce(): void
    {
        $creator = (new Creator())
            ->setCreatorId('CREATOR')
            ->setPhotoUrls(['scritch_photo_1', 'scritch_photo_2'])
            ->setMiniatureUrls(['scritch_wrongMiniature_1', 'scritch_wrongMiniature_2']);

        $this->subject->updateCreatorMiniaturesFor($creator, true);

        self::assertSame(['scritch_miniature_1', 'scritch_miniature_2'], $creator->getMiniatureUrls());
    }

    public function testMiniaturesGetClearedWhenPhotosAreEmpty(): void
    {
        $creator = (new Creator())
            ->setCreatorId('CREATOR')
            ->setPhotoUrls([])
            ->setMiniatureUrls(['furtrack_obsoleteMiniature_1', 'furtrack_obsoleteMiniature_2']);

        $this->subject->updateCreatorMiniaturesFor($creator, true);

        self::assertSame([], $creator->getMiniatureUrls());
    }

    public function testNothingChangesWhenAtLeastOnePhotoIsUnsupported(): void
    {
        $creator = (new Creator())
            ->setCreatorId('CREATOR')
            ->setMiniatureUrls([]);

        // All photos are wrong
        $creator->setPhotoUrls(['other_photo_1', 'other_photo_2']);
        $this->subject->updateCreatorMiniaturesFor($creator, true);
        self::assertSame([], $creator->getMiniatureUrls());

        // A single photo is wrong
        $creator->setPhotoUrls(['scritch_photo_1', 'other_photo_2']);
        $this->subject->updateCreatorMiniaturesFor($creator, true);
        self::assertSame([], $creator->getMiniatureUrls());
    }

    public function testAddingMiniaturesWorksProperly(): void
    {
        $creator = (new Creator())
            ->setCreatorId('CREATOR')
            ->setPhotoUrls(['scritch_photo_1', 'furtrack_photo_2'])
            ->setMiniatureUrls([]);

        $this->subject->updateCreatorMiniaturesFor($creator, false);

        self::assertSame(['scritch_miniature_1', 'furtrack_miniature_2'], $creator->getMiniatureUrls());
    }

    public function testUpdatingMiniaturesWorksProperly(): void
    {
        $creator = (new Creator())
            ->setCreatorId('CREATOR')
            ->setPhotoUrls(['scritch_photo_1', 'furtrack_photo_2'])
            ->setMiniatureUrls(['furtrack_miniature_1', 'scritch_miniature_2']);

        $this->subject->updateCreatorMiniaturesFor($creator, true);

        self::assertSame(['scritch_miniature_1', 'furtrack_miniature_2'], $creator->getMiniatureUrls());
    }

    public function testReorderingMiniaturesWorksProperly(): void
    {
        $creator = (new Creator())
            ->setCreatorId('CREATOR')
            ->setPhotoUrls(['furtrack_photo_1', 'furtrack_photo_2'])
            ->setMiniatureUrls(['furtrack_miniature_2', 'furtrack_miniature_1']);

        $this->subject->updateCreatorMiniaturesFor($creator, true);

        self::assertSame(['furtrack_miniature_1', 'furtrack_miniature_2'], $creator->getMiniatureUrls());
    }

    public function testPhotosAreNotDeduplicatedToAvoidRefetchingDuringEachUpdate(): void
    {
        $creator = (new Creator())
            ->setCreatorId('CREATOR')
            ->setPhotoUrls(['furtrack_photo_1', 'furtrack_photo_2', 'furtrack_photo_2'])
            ->setMiniatureUrls([]);

        $this->subject->updateCreatorMiniaturesFor($creator, false);

        self::assertSame(['furtrack_miniature_1', 'furtrack_miniature_2', 'furtrack_miniature_2'], $creator->getMiniatureUrls());
    }
}
