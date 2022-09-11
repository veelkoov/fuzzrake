<?php

declare(strict_types=1);

namespace App\Tracking\OfferStatus;

use App\Tracking\Issue;
use DateTimeImmutable;

class OfferStatusResult
{
    /**
     * @param OfferStatus[] $offerStatuses
     * @param Issue[]       $issues
     */
    public function __construct(
        public readonly array $offerStatuses,
        public readonly ?DateTimeImmutable $lastCsUpdate,
        public readonly bool $csTrackerIssue,
        public readonly array $issues,
    ) {
    }
}
