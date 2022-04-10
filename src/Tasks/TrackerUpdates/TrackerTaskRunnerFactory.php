<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Entity\Artisan as ArtisanE;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\BasePrices\BasePricesTrackerTask;
use App\Tasks\TrackerUpdates\Commissions\CommissionsTrackerTask;
use App\Tracker\OfferStatusParser;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\Printer;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TrackerTaskRunnerFactory
{
    final public const COMMISSIONS = 'commissions';
    final public const BASE_PRICES = 'base-prices';

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

    public function get(string $mode, bool $refetch, bool $commit, SymfonyStyle $io): TrackerTaskRunner
    {
        return new TrackerTaskRunner(
            $this->getTrackerTask($mode),
            $this->logger,
            $this->entityManager,
            $this->webpageSnapshotManager,
            $this->fdvFactory->create(new Printer($io)),
            $refetch,
            $commit,
            $io,
        );
    }

    private function getTrackerTask(string $mode): TrackerTaskInterface
    {
        return match ($mode) {
            self::BASE_PRICES => new BasePricesTrackerTask($this->artisanRepository, $this->logger, $this->webpageSnapshotManager),
            self::COMMISSIONS => new CommissionsTrackerTask($this->artisanRepository, $this->logger, $this->webpageSnapshotManager, $this->parser),
            default           => throw new InvalidArgumentException(sprintf('Format must be one of: "%s", "%s"', self::COMMISSIONS, self::BASE_PRICES)),
        };
    }
}
