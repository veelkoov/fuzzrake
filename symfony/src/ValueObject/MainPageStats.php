<?php

declare(strict_types=1);

namespace App\ValueObject;

use DateTimeImmutable;

readonly class MainPageStats
{
    public function __construct(
        public ?int $totalArtisansCount,
        public ?int $activeArtisansCount,
        public ?int $countryCount,
        public ?DateTimeImmutable $lastDataUpdateTimeUtc,
    ) {
    }
}
