<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\BasePrices;

use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\ArtisanUpdatesInterface;
use App\Tasks\TrackerUpdates\TrackerTaskInterface;
use App\Utils\Tracking\TrackerException;
use Psr\Log\LoggerInterface;

class BasePricesTrackerTask implements TrackerTaskInterface
{
    public function __construct(
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
        return []; // TODO: Implement getUrlsToPrefetch() method. See #29
    }

    /**
     * @noinspection PhpDocRedundantThrowsInspection
     *
     * @return ArtisanUpdatesInterface[]
     *
     * @throws TrackerException
     */
    public function getUpdates(): array
    {
        return []; // TODO: Implement perform() method. See #29
    }
}
