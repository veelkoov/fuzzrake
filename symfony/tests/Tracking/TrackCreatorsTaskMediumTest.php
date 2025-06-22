<?php

declare(strict_types=1);

namespace App\Tests\Tracking;

use App\Entity\Creator as CreatorE;
use App\Entity\CreatorUrl;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Tracking\OfferStatusTracker;
use App\Tracking\TrackCreatorsTask;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\ValueObject\Messages\InitiateTrackingV1;
use App\ValueObject\Messages\TrackCreatorsV1;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class TrackCreatorsTaskMediumTest extends FuzzrakeKernelTestCase
{
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
                self::assertSame(3, $message->idsOfCreators->count());
                self::assertEqualsCanonicalizing($expectedIds, $message->idsOfCreators->getValuesArray());

                return new Envelope($message);
            });

        $subject = new TrackCreatorsTask(self::getEM(),
            self::getEM()->getRepository(CreatorUrl::class),
            self::getEM()->getRepository(CreatorE::class),
            $messageBusMock,
            self::createStub(OfferStatusTracker::class),
        );

        $subject->initiateTrackingMessageHandler(new InitiateTrackingV1());
    }
}
