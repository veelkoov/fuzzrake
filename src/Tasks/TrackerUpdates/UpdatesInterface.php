<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Entity\ArtisanUrl;
use App\Utils\Tracking\TrackerException;

interface UpdatesInterface
{
    /**
     * @return ArtisanUrl[]
     */
    public function getUrlsToPrefetch(): array;

    /**
     * @return AnalysisResultInterface[]
     *
     * @throws TrackerException
     */
    public function perform(): array;
}
