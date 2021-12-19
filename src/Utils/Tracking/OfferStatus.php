<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

class OfferStatus
{
    public function __construct(
        private readonly string $offer,
        private readonly bool $status,
    ) {
    }

    public function getOffer(): string
    {
        return $this->offer;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }
}
