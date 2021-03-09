<?php

declare(strict_types=1);

namespace App\Tasks\TrackerUpdates;

final class TrackerUpdatesConfig
{
    public function __construct(
        private bool $refetch,
        private bool $dryRun,
        private bool $updateCommissions,
        private bool $updateBasePrices,
    ) {
    }

    public function isRefetch(): bool
    {
        return $this->refetch;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function isUpdateCommissions(): bool
    {
        return $this->updateCommissions;
    }

    public function isUpdateBasePrices(): bool
    {
        return $this->updateBasePrices;
    }
}
