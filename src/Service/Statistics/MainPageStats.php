<?php

declare(strict_types=1);

namespace App\Service\Statistics;

use DateTimeImmutable;

class MainPageStats
{
    public function __construct(
        private readonly ?int $activeArtisansCount,
        private readonly ?int $countryCount,
        private readonly ?DateTimeImmutable $lastDataUpdateTimeUtc,
        private readonly ?DateTimeImmutable $lastSystemUpdateTimeUtc,
    ) {
    }

    public function getActiveArtisansCount(): ?int
    {
        return $this->activeArtisansCount;
    }

    public function getCountryCount(): ?int
    {
        return $this->countryCount;
    }

    public function getLastDataUpdateTimeUtc(): ?DateTimeImmutable
    {
        return $this->lastDataUpdateTimeUtc;
    }

    public function getLastSystemUpdateTimeUtc(): ?DateTimeImmutable
    {
        return $this->lastSystemUpdateTimeUtc;
    }
}
