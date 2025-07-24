<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Entity\Creator as CreatorE;
use App\Repository\CreatorRepository;
use App\Repository\CreatorUrlRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Enforce;
use App\ValueObject\CacheTags;
use App\ValueObject\Messages\InitiateTrackingV1;
use App\ValueObject\Messages\InvalidateCacheTagsV1;
use App\ValueObject\Messages\TrackCreatorsV1;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Veelkoov\Debris\Base\DList;
use Veelkoov\Debris\IntList;

final class TrackCreatorsTask
{
    private const int NUMBER_OF_TRACKED_CREATORS_PER_CHUNK = 50;
    public const int MAX_RETRIES = 1;

    public function __construct(
        #[Autowire(service: 'monolog.logger.tracking')]
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly CreatorUrlRepository $creatorUrlRepository,
        private readonly CreatorRepository $creatorRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly CreatorTracker $tracker,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    #[AsMessageHandler]
    public function initiateTrackingMessageHandler(InitiateTrackingV1 $message): void
    {
        $idChunks = array_chunk(
            $this->creatorUrlRepository->getIdsOfActiveCreatorsHavingAnyTrackedUrl(),
            self::NUMBER_OF_TRACKED_CREATORS_PER_CHUNK,
        );

        $this->logger->info('Dispatching '.count($idChunks).' '.TrackCreatorsV1::class.' messages.');

        foreach ($idChunks as $idChunk) {
            $this->messageBus->dispatch(new TrackCreatorsV1(
                new IntList($idChunk),
                $message->retriesLimit,
                $message->refetchPages,
            ));
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

        $failedIds = new DList($creators)
            // Tracking happens here.
            ->filterNot(fn (CreatorE $creator) => $this->tracker->track(Creator::wrap($creator),
                $message->retryAllowed(), $message->refetchPages))
            ->mapInto(static fn (CreatorE $creator) => Enforce::int($creator->getId()), new IntList());

        $this->handleFailedIds($failedIds, $message);

        // Clear cache if retries are not allowed (tracked must have been updated), or at least one creator was updated.
        if (!$message->retryAllowed() || $message->idsOfCreators->count() !== $failedIds->count()) {
            $this->messageBus->dispatch(new InvalidateCacheTagsV1(CacheTags::CREATORS, CacheTags::TRACKING));
        }

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

        if (!$message->retryAllowed()) {
            $this->logger->warning("Maximum retries reached for {$failedIds->count()} creators tracking.");

            return;
        }

        $this->logger->warning("Scheduling retry of {$failedIds->count()} out of {$message->idsOfCreators->count()} track jobs.");

        $this->messageBus->dispatch(
            new TrackCreatorsV1($failedIds, $message->retriesLimit - 1, $message->refetchPages),
            [DelayStamp::delayFor(new DateInterval('PT2H'))], // grep-code-tracking-frequency
        );
    }
}
