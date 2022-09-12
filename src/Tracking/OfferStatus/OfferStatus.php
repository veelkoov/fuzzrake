<?php

declare(strict_types=1);

namespace App\Tracking\OfferStatus;

class OfferStatus
{
    public function __construct(
        public readonly string $offer,
        public readonly bool $status,
    ) {
    }
}
