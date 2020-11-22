<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Entity\ArtisanUrl;

interface UpdatesInterface
{
    /**
     * @return ArtisanUrl[]
     */
    public function getUrlsToPrefetch(): array;

    /**
     * @return AnalysisResultInterface[]
     */
    public function perform(): array;
}
