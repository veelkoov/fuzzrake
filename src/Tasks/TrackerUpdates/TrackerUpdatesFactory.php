<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\BasePrices\BasePricesUpdates;
use App\Tasks\TrackerUpdates\Commissions\CommissionsUpdates;
use App\Utils\Tracking\CommissionsStatusParser;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TrackerUpdatesFactory
{
    private ArtisanRepository $artisanRepository;

    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private WebpageSnapshotManager $webpageSnapshotManager,
        private CommissionsStatusParser $parser,
    ) {
        $this->artisanRepository = $entityManager->getRepository(Artisan::class);
    }

    public function get(SymfonyStyle $io, TrackerUpdatesConfig $config): TrackerUpdates
    {
        return new TrackerUpdates($this->logger, $this->entityManager, $this->webpageSnapshotManager, $config, $this->getUpdatesObjects($config), $io);
    }

    /**
     * @return UpdatesInterface[]
     */
    private function getUpdatesObjects(TrackerUpdatesConfig $config): array
    {
        $updates = [];

        if ($config->isUpdateBasePrices()) {
            $updates[] = new BasePricesUpdates($config, $this->artisanRepository, $this->logger, $this->webpageSnapshotManager);
        }

        if ($config->isUpdateCommissions()) {
            $updates[] = new CommissionsUpdates($config, $this->artisanRepository, $this->logger, $this->webpageSnapshotManager, $this->parser);
        }

        return $updates;
    }
}
