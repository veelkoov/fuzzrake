<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use App\Utils\Data\ArtisanChanges;
use Symfony\Component\Console\Style\SymfonyStyle;

interface ArtisanUpdatesInterface
{
    public function report(SymfonyStyle $io): void;

    /**
     * @return ArtisanChanges[]
     */
    public function getChanges(): array;
}
