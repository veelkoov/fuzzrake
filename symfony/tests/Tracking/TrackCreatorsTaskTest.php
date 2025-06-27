<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Entity\Creator as CreatorE;
use App\Entity\CreatorUrl;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Tests\TestUtils\Cases\Traits\MessageBusTrait;
use App\Tracking\OfferStatusTracker;
use App\Tracking\TrackCreatorsTask;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\ValueObject\Messages\InitiateTrackingV1;
use App\ValueObject\Messages\TrackCreatorsV1;
use PHPUnit\Framework\Attributes\Medium;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;
use Veelkoov\Debris\IntList;

#[Medium]
class TrackCreatorsTaskTest extends FuzzrakeKernelTestCase
{
    use MessageBusTrait;

    /**
     * @throws Throwable
     */
    public function testInitiatingTracking(): void
    {
        $creator1 = (new Creator())->setCreatorId('TEST001')->setCommissionsUrls(['creator1_A']);
        $creator2 = (new Creator())->setCreatorId('TEST002')->setCommissionsUrls(['creator2_A', 'creator2_B']);
        $creator3 = (new Creator())->setCreatorId('TEST003')->setWebsiteUrl('creator3_A');
        $creator4 = (new Creator())->setCreatorId('TEST004')->setWebsiteUrl('creator4_A')->setCommissionsUrls(['creator4_B']);

        self::persistAndFlush($creator1, $creator2, $creator3, $creator4);

        $expectedIds = [$creator1->getId(), $creator2->getId(), $creator4->getId()];

        $messageBusMock = $this->createMock(MessageBusInterface::class);
        $messageBusMock->expects(self::once())->method('dispatch')
            ->willReturnCallback(function (TrackCreatorsV1 $message) use ($expectedIds): Envelope {
                self::assertCount(3, $message->idsOfCreators);
                self::assertEqualsCanonicalizing($expectedIds, $message->idsOfCreators->getValuesArray());

                return new Envelope($message);
            });

        $subject = new TrackCreatorsTask(self::getEM(),
            self::getEM()->getRepository(CreatorUrl::class),
            self::getEM()->getRepository(CreatorE::class),
            $messageBusMock,
            self::createStub(OfferStatusTracker::class),
            self::createStub(LoggerInterface::class),
        );

        $subject->initiateTrackingMessageHandler(new InitiateTrackingV1());
    }

    /**
     * @throws Throwable
     */
    public function testTrackCreatorsMessageHandler(): void
    {
        $creator1 = (new Creator())->setCreatorId('TEST001')->setCommissionsUrls(['creator1_A']);
        $creator2 = (new Creator())->setCreatorId('TEST002')->setCommissionsUrls(['creator2_A']);
        $creator3 = (new Creator())->setCreatorId('TEST003')->setCommissionsUrls(['creator3_A']);
        $creator4 = (new Creator())->setCreatorId('TEST004')->setCommissionsUrls(['creator4_A']);

        self::persistAndFlush($creator1, $creator2, $creator3, $creator4);

        $intList = (new IntList([
            $creator1->getId() ?? 0,
            $creator2->getId() ?? 0,
            $creator3->getId() ?? 0,
            $creator4->getId() ?? 0,
        ]))->unique();
        self::assertCount(4, $intList);

        // Tracker which will "fail" every second creator (by ID)
        $trackerMock = self::createMock(OfferStatusTracker::class);
        $trackerMock->method('update')->willReturnCallback(
            static fn (Creator $creator) => ($creator->getId() ?? 0) % 2 === 0);

        $subject = new TrackCreatorsTask(self::getEM(),
            self::getEM()->getRepository(CreatorUrl::class),
            self::getEM()->getRepository(CreatorE::class),
            self::getContainerService(MessageBusInterface::class),
            $trackerMock,
            self::createStub(LoggerInterface::class),
        );

        $subject->trackCreatorsMessageHandler(new TrackCreatorsV1($intList));

        $message = self::getQueued(TrackCreatorsV1::class)->single();
        self::clearQueue();

        self::assertCount(2, $message->idsOfCreators);
        self::assertEqualsCanonicalizing(
            $intList->filter(static fn (int $id) => 1 === $id % 2)->getValuesArray(),
            $message->idsOfCreators->getValuesArray(),
        );
        self::assertSame(1, $message->retryNumber);

        $subject->trackCreatorsMessageHandler($message);

        self::assertMessageBusQueueEmpty();
    }
}
