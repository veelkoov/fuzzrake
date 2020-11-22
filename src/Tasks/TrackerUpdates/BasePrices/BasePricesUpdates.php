<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\BasePrices;

use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\AnalysisResultInterface;
use App\Tasks\TrackerUpdates\TrackerUpdatesConfig;
use App\Tasks\TrackerUpdates\UpdatesInterface;
use Psr\Log\LoggerInterface;

final class BasePricesUpdates implements UpdatesInterface
{
    private TrackerUpdatesConfig $config;
    private ArtisanRepository $repository;
    private LoggerInterface $logger;
    private WebpageSnapshotManager $snapshots;

    public function __construct(TrackerUpdatesConfig $config, ArtisanRepository $repository, LoggerInterface $logger, WebpageSnapshotManager $snapshots)
    {
        $this->repository = $repository;
        $this->config = $config;
        $this->logger = $logger;
        $this->snapshots = $snapshots;
    }

    /**
     * @return ArtisanUrl[]
     */
    public function getUrlsToPrefetch(): array
    {
        return []; // TODO: Implement getUrlsToPrefetch() method.
    }

    /**
     * @return AnalysisResultInterface[]
     */
    public function perform(): array
    {
        return []; // TODO: Implement perform() method.
    }
}
