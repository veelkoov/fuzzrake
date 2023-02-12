<?php

declare(strict_types=1);

namespace App\Tracking\OfferStatus;

readonly class OfferStatus
{
    public function __construct(
        public string $offer,
        public bool $status,
    ) {
    }
}
