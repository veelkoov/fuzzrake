<?php

declare(strict_types=1);

namespace App\Service\Statistics;

use DateTimeInterface;

class MainPageStats
{
    public function __construct(
        private readonly ?int $activeArtisansCount,
        private readonly ?int $countryCount,
        private readonly ?DateTimeInterface $lastDataUpdateTimeUtc,
        private readonly ?DateTimeInterface $lastSystemUpdateTimeUtc,
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

    public function getLastDataUpdateTimeUtc(): ?DateTimeInterface
    {
        return $this->lastDataUpdateTimeUtc;
    }

    public function getLastSystemUpdateTimeUtc(): ?DateTimeInterface
    {
        return $this->lastSystemUpdateTimeUtc;
    }
}
