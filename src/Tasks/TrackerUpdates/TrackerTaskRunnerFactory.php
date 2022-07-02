<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Entity\Artisan as ArtisanE;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tracker\OfferStatusParser;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\Printer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TrackerTaskRunnerFactory
{
    final public const COMMISSIONS = 'commissions';

    private readonly ArtisanRepository $artisanRepository;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly WebpageSnapshotManager $webpageSnapshotManager,
        private readonly OfferStatusParser $parser,
        private readonly FdvFactory $fdvFactory,
    ) {
        $this->artisanRepository = $entityManager->getRepository(ArtisanE::class);
    }

    public function get(bool $refetch, bool $commit, SymfonyStyle $io): TrackerTaskRunner
    {
        return new TrackerTaskRunner(
            new CommissionsTrackerTask($this->artisanRepository, $this->logger, $this->webpageSnapshotManager, $this->parser),
            $this->logger,
            $this->entityManager,
            $this->webpageSnapshotManager,
            $this->fdvFactory->create(new Printer($io)),
            $refetch,
            $commit,
            $io,
        );
    }
}
