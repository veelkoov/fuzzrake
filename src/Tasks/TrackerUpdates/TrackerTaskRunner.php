<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Service\WebpageSnapshotManager;
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
            $this->replace($updates);
        }
    }

    private function setupIo(SymfonyStyle $io): void
    {
        $this->io = $io;
        $this->io->getFormatter()->setStyle('open', new OutputFormatterStyle('green'));
        $this->io->getFormatter()->setStyle('closed', new OutputFormatterStyle('red'));
        $this->io->getFormatter()->setStyle('context', new OutputFormatterStyle('blue'));
    }

    /**
     * @param ArtisanUpdatesInterface[] $results
     */
    private function report(array $results): void
    {
        foreach ($results as $result) {
            $result->report($this->io);
        }
    }

    /**
     * @param ArtisanUpdatesInterface[] $results
     */
    private function replace(array $results): void
    {
        foreach ($results as $result) {
            foreach ($result->getRemovedEntities() as $entity) {
                $this->entityManager->remove($entity);
            }

            foreach ($result->getCreatedEntities() as $entity) {
                $this->entityManager->persist($entity);
            }
        }
    }
}
