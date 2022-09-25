<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tracking\OfferStatus\OfferStatusProcessor;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\Printer;
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
        private readonly OfferStatusProcessor $offerStatusProcessor,
        private readonly FdvFactory $fdvFactory,
    ) {
    }

    public function get(bool $refetch, bool $commit, SymfonyStyle $io): StatusTracker
    {
        return new StatusTracker(
            $this->trackingLogger,
            $this->entityManager,
            $this->artisanRepository,
            $this->offerStatusProcessor,
            $this->webpageSnapshotManager,
            $this->fdvFactory->create(new Printer($io)),
            $refetch,
            $commit,
            $io,
        );
    }
}
