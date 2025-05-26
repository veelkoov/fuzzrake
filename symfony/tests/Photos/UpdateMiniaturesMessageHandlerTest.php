<?php

declare(strict_types=1);

namespace App\Tests\Photos;

use App\Entity\Creator as CreatorE;
use App\Photos\MiniaturesUpdater;
use App\Photos\UpdateMiniaturesMessageHandler;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\ValueObject\Messages\UpdateMiniaturesV1;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Veelkoov\Debris\Base\DSet;

/**
 * @small
 */
class UpdateMiniaturesMessageHandlerTest extends TestCase
{
    public function testSingleCreatorUpdateMessageHandling(): void
    {
        $creatorE = new CreatorE();
        $creatorId = 5;

        $creatorRepositoryMock = $this->createMock(CreatorRepository::class);
        $creatorRepositoryMock->expects(self::once())->method('find')->with($creatorId)
            ->willReturn($creatorE);

        $updaterMock = $this->createMock(MiniaturesUpdater::class);
        $updaterMock->expects(self::once())->method('updateCreatorMiniaturesFor')
            ->willReturnCallback(function (Creator $creator, bool $force) use ($creatorE) {
                self::assertSame($creatorE, $creator->getCreator());
                self::assertTrue($force, 'Force should be true for a single-creator update.');
            });

        $subject = $this->getSubject($creatorRepositoryMock, $updaterMock);

        $subject->handle(new UpdateMiniaturesV1($creatorId));
    }

    public function testAllCreatorsUpdateMessageHandling(): void
    {
        $creators = new DSet([new CreatorE(), new CreatorE(), new CreatorE()]);

        $creatorRepositoryMock = $this->createMock(CreatorRepository::class);
        $creatorRepositoryMock->expects(self::once())->method('getAllPaged')
            ->willReturnCallback(function () use ($creators) {yield from $creators; });

        $updaterMock = $this->createMock(MiniaturesUpdater::class);

        // Expect that the update method gets called for all creators in the repository.
        $updaterMock->expects(self::exactly($creators->count()))->method('updateCreatorMiniaturesFor')
            ->willReturnCallback(function (Creator $creator, bool $force) use ($creators) {
                self::assertTrue($creators->contains($creator->getCreator()));
                self::assertFalse($force, 'Force should be false for an all-creators update.');

                $creators->remove($creator->getCreator());
            });

        $subject = $this->getSubject($creatorRepositoryMock, $updaterMock);

        $subject->handle(new UpdateMiniaturesV1(null));

        self::assertSame(0, $creators->count(), 'Sanity check failed.');
    }

    private function getSubject(CreatorRepository $creatorRepositoryMock, MiniaturesUpdater $updaterMock): UpdateMiniaturesMessageHandler
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())->method('flush');

        return new UpdateMiniaturesMessageHandler($loggerMock, $entityManagerMock, $creatorRepositoryMock, $updaterMock);
    }
}
