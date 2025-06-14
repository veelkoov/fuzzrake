<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Repository\CreatorUrlRepository;
use App\ValueObject\Messages\InitiateTrackingV1;
use App\ValueObject\Messages\TrackCreatorsV1;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class InitiateTrackingTask
{
    private const int NUMBER_OF_TRACKED_CREATORS_PER_CHUNK = 50;

    public function __construct(
        private readonly CreatorUrlRepository $creatorUrlRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    #[AsMessageHandler]
    public function execute(InitiateTrackingV1 $_): void
    {
        $idChunks = array_chunk(
            $this->creatorUrlRepository->getIdsOfActiveCreatorsHavingAnyTrackedUrl(),
            self::NUMBER_OF_TRACKED_CREATORS_PER_CHUNK,
        );

        foreach ($idChunks as $idChunk) {
            $this->messageBus->dispatch(new TrackCreatorsV1($idChunk));
        }
    }
}
