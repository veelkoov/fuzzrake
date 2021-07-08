<?php

declare(strict_types=1);

namespace App\Utils\Tracking;

class OfferStatus
{
    public function __construct(
        private string $offer,
        private bool $status,
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
