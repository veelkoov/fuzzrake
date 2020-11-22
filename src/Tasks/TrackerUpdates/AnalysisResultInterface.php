<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

use Symfony\Component\Console\Style\SymfonyStyle;

interface AnalysisResultInterface
{
    public function report(SymfonyStyle $io): void;

    /**
     * @return object[]
     */
    public function getNewEntities(): array;
}
