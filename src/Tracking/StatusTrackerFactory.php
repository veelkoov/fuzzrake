<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tracking\OfferStatus\OffersStatusesProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StatusTrackerFactory
{
    public function __construct(
        private readonly LoggerInterface $trackingLogger,
        private readonly ArtisanRepository $artisanRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly WebpageSnapshotManager $webpageSnapshotManager,
        private readonly OffersStatusesProcessor $offerStatusProcessor,
    ) {
    }

    public function get(bool $refetch, SymfonyStyle $io): StatusTracker
    {
        return new StatusTracker(
            $this->trackingLogger,
            $this->entityManager,
            $this->artisanRepository,
            $this->offerStatusProcessor,
            $this->webpageSnapshotManager,
            $refetch,
            $io,
        );
    }
}
