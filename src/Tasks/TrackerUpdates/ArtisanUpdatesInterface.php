<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use Symfony\Component\Console\Style\SymfonyStyle;

interface ArtisanUpdatesInterface
{
    public function report(SymfonyStyle $io): void;

    /**
     * @return object[]
     */
    public function getCreatedEntities(): array;

    /**
     * @return object[]
     */
    public function getRemovedEntities(): array;
}
