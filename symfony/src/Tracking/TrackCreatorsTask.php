<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Entity\Creator as CreatorE;
use App\Repository\CreatorRepository;
use App\Repository\CreatorUrlRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Enforce;
use App\ValueObject\Messages\InitiateTrackingV1;
use App\ValueObject\Messages\TrackCreatorsV1;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Veelkoov\Debris\Base\DList;
use Veelkoov\Debris\IntList;

final class TrackCreatorsTask
{
    private const int NUMBER_OF_TRACKED_CREATORS_PER_CHUNK = 50;
    private const int MAX_RETRIES = 1;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CreatorUrlRepository $creatorUrlRepository,
        private readonly CreatorRepository $creatorRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly OfferStatusTracker $tracker,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    #[AsMessageHandler]
    public function initiateTrackingMessageHandler(InitiateTrackingV1 $_): void
    {
        $idChunks = array_chunk(
            $this->creatorUrlRepository->getIdsOfActiveCreatorsHavingAnyTrackedUrl(),
            self::NUMBER_OF_TRACKED_CREATORS_PER_CHUNK,
        );

        foreach ($idChunks as $idChunk) {
            $this->messageBus->dispatch(new TrackCreatorsV1(new IntList($idChunk)));
        }
    }

    /**
     * @throws ExceptionInterface
     */
    #[AsMessageHandler]
    public function trackCreatorsMessageHandler(TrackCreatorsV1 $message): void
    {
        $creators = $this->creatorRepository->getWithIds($message->idsOfCreators);

        if (count($creators) !== $message->idsOfCreators->count()) {
            // A maker could have been removed between message dispatch and handling.
            $this->logger->warning('Retrieved less creators than given IDs. Unless commonly happening, this can be ignored.');
        }

        $failedIds = (new DList($creators))
            // Tracking happens here.
            ->filterNot(fn (CreatorE $creator) => $this->tracker->update(Creator::wrap($creator)))
            ->mapInto(static fn (CreatorE $creator) => Enforce::int($creator->getId()), new IntList());

        $this->handleFailedIds($failedIds, $message);

        $this->entityManager->flush();
    }

    /**
     * @throws ExceptionInterface
     */
    private function handleFailedIds(IntList $failedIds, TrackCreatorsV1 $message): void
    {
        if ($failedIds->isEmpty()) {
            return;
        }

        if ($message->retryNumber >= self::MAX_RETRIES) {
            $this->logger->warning("Maximum retries reached for {$failedIds->count()} creators tracking.");

            return;
        }

        $this->logger->warning("Scheduling retry of {$failedIds->count()} out of {$message->idsOfCreators->count()} track jobs.");

        $this->messageBus->dispatch(
            new TrackCreatorsV1($failedIds, $message->retryNumber + 1),
            [DelayStamp::delayFor(new DateInterval('PT2H'))], // grep-code-tracking-frequency
        );
    }
}
