<?php

declare(strict_types=1);

namespace App\Tasks\StatusTracker;

use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tracker\OfferStatusParser;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\Printer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TaskIOFactory
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ArtisanRepository $artisanRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly WebpageSnapshotManager $webpageSnapshotManager,
        private readonly OfferStatusParser $parser,
        private readonly FdvFactory $fdvFactory,
    ) {
    }

    public function get(bool $refetch, bool $commit, SymfonyStyle $io): TaskIO
    {
        return new TaskIO(
            $this->logger,
            $this->entityManager,
            $this->artisanRepository,
            $this->parser,
            $this->webpageSnapshotManager,
            $this->fdvFactory->create(new Printer($io)),
            $refetch,
            $commit,
            $io,
        );
    }
}
