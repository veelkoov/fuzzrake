<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Service\WebpageSnapshotManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TrackerUpdates
{
    private SymfonyStyle $io;

    /**
     * @param UpdatesInterface[] $updates
     */
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private WebpageSnapshotManager $snapshots,
        private TrackerUpdatesConfig $config,
        private array $updates,
        SymfonyStyle $io,
    ) {
        $this->setupIo($io);
    }

    public function updateAll()
    {
        $this->prefetchUrls();

        foreach ($this->updates as $update) {
            $results = $update->perform();

            $this->report($results);
            $this->record($results);
        }
    }

    private function prefetchUrls(): void
    {
        $urls = array_merge(...array_map(fn (UpdatesInterface $updates) => $updates->getUrlsToPrefetch(), $this->updates));

        $this->snapshots->prefetchUrls($urls, $this->config->isRefetch(), $this->io);
    }

    private function setupIo(SymfonyStyle $io): void
    {
        $this->io = $io;
        $this->io->getFormatter()->setStyle('open', new OutputFormatterStyle('green'));
        $this->io->getFormatter()->setStyle('closed', new OutputFormatterStyle('red'));
        $this->io->getFormatter()->setStyle('context', new OutputFormatterStyle('blue'));
    }

    /**
     * @param AnalysisResultInterface[] $results
     */
    private function report(array $results): void
    {
        foreach ($results as $result) {
            $result->report($this->io);
        }
    }

    /**
     * @param AnalysisResultInterface[] $results
     */
    private function record(array $results): void
    {
        foreach ($results as $result) {
            foreach ($result->getNewEntities() as $newEntity) {
                $this->entityManager->persist($newEntity);
            }
        }
    }
}
