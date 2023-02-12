<?php

declare(strict_types=1);

namespace App\Tracking\OfferStatus;

use App\Tracking\Issue;
use DateTimeImmutable;

readonly class OffersStatusesResult
{
    /**
     * @param list<OfferStatus> $offersStatuses
     * @param list<Issue>       $issues
     */
    public function __construct(
        public array $offersStatuses,
        public ?DateTimeImmutable $lastCsUpdate,
        public bool $csTrackerIssue,
        public array $issues,
    ) {
    }
}
