<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates\BasePrices;

use App\Entity\ArtisanUrl;
use App\Repository\ArtisanRepository;
use App\Service\WebpageSnapshotManager;
use App\Tasks\TrackerUpdates\TrackerTaskInterface;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Tracking\TrackerException;
use Psr\Log\LoggerInterface;

class BasePricesTrackerTask implements TrackerTaskInterface
{
    /** @noinspection PhpPropertyOnlyWrittenInspection */
    public function __construct(
        private readonly ArtisanRepository $repository,
        private readonly LoggerInterface $logger,
        private readonly WebpageSnapshotManager $snapshots,
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
     * @return ArtisanChanges[]
     *
     * @throws TrackerException
     */
    public function getUpdates(): array
    {
        return []; // TODO: Implement perform() method. See #29
    }
}
