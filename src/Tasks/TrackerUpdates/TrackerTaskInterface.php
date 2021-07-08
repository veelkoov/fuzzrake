<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Entity\ArtisanUrl;
use App\Utils\Data\ArtisanChanges;
use App\Utils\Tracking\TrackerException;

interface TrackerTaskInterface
{
    /**
     * @return ArtisanUrl[]
     */
    public function getUrlsToPrefetch(): array;

    /**
     * @return ArtisanChanges[]
     *
     * @throws TrackerException
     */
    public function getUpdates(): array;
}
