<?php

declare(strict_types=1);

namespace App\Reviews;

final readonly class DailySubmissionPostsSummary
{
    public function __construct(
        public string $date,
        public string $nicknames,
        public int $count,
        public int $submissionId,
    ) {
    }
}
