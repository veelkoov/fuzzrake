<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Service\WebpageSnapshotManager;
use App\Utils\Tracking\CommissionsStatusParser;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TrackerUpdatesFactory
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private WebpageSnapshotManager $webpageSnapshotManager;
    private CommissionsStatusParser $parser;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        WebpageSnapshotManager $webpageSnapshotManager
    ) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->webpageSnapshotManager = $webpageSnapshotManager;
        $this->parser = new CommissionsStatusParser();
    }

    public function get(SymfonyStyle $io, TrackerUpdatesConfig $config): TrackerUpdates
    {
        return new TrackerUpdates($this->logger, $this->entityManager, $this->webpageSnapshotManager, $io, $config);
    }
}
