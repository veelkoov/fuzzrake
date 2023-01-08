<?php

declare(strict_types=1);

namespace App\ValueObject;

use DateTimeImmutable;

class MainPageStats
{
    public function __construct(
        public readonly ?int $totalArtisansCount,
        public readonly ?int $activeArtisansCount,
        public readonly ?int $countryCount,
        public readonly ?DateTimeImmutable $lastDataUpdateTimeUtc,
        public readonly ?DateTimeImmutable $lastSystemUpdateTimeUtc,
    ) {
    }
}
