<?php

declare(strict_types=1);

namespace App\Tracking\OfferStatus;

use App\Tracking\Issue;
use DateTimeImmutable;

class OffersStatusesResult
{
    /**
     * @param list<OfferStatus> $offersStatuses
     * @param list<Issue>       $issues
     */
    public function __construct(
        public readonly array $offersStatuses,
        public readonly ?DateTimeImmutable $lastCsUpdate,
        public readonly bool $csTrackerIssue,
        public readonly array $issues,
    ) {
    }
}
