<?php

declare(strict_types=1);

namespace App\Service\Statistics;

use DateTimeInterface;

class MainPageStats
{
    public function __construct(
        private ?int $activeArtisansCount,
        private ?int $countryCount,
        private ?DateTimeInterface $lastDataUpdateTimeUtc,
        private ?DateTimeInterface $lastSystemUpdateTimeUtc,
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
