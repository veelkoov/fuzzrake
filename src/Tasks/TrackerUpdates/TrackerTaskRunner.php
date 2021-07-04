<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Service\WebpageSnapshotManager;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Tracking\TrackerException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class TrackerTaskRunner
{
    private SymfonyStyle $io;

    public function __construct(
        private TrackerTaskInterface $trackerTask,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private WebpageSnapshotManager $snapshots,
        private bool $refetch,
        private bool $commit,
        SymfonyStyle $io,
    ) {
        $this->setupIo($io);
    }

    /**
     * @throws TrackerException
     */
    public function performUpdates()
    {
        $this->snapshots->prefetchUrls($this->trackerTask->getUrlsToPrefetch(), $this->refetch, $this->io);

        $updates = $this->trackerTask->getUpdates();

        $this->report($updates);

        if ($this->commit) {
            foreach ($updates as $update) {
                $update->apply();
            }
        }
    }

    /**
     * @param ArtisanChanges[] $results
     */
    private function report(array $results): void
    {
        // TODO
    }
}
