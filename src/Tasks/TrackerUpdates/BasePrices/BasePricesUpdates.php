<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\BasePrices;

use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\AnalysisResultInterface;
use App\Tasks\TrackerUpdates\TrackerUpdatesConfig;
use App\Tasks\TrackerUpdates\UpdatesInterface;
use App\Utils\Tracking\TrackerException;
use Psr\Log\LoggerInterface;

final class BasePricesUpdates implements UpdatesInterface
{
    public function __construct(
        private TrackerUpdatesConfig $config,
        private ArtisanRepository $repository,
        private LoggerInterface $logger,
        private WebpageSnapshotManager $snapshots,
    ) {
    }

    /**
     * @return ArtisanUrl[]
     */
    public function getUrlsToPrefetch(): array
    {
        return []; // TODO: Implement getUrlsToPrefetch() method.
    }

    /**
     * @noinspection PhpDocRedundantThrowsInspection
     *
     * @return AnalysisResultInterface[]
     *
     * @throws TrackerException
     */
    public function perform(): array
    {
        return []; // TODO: Implement perform() method.
    }
}
