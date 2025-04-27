<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Entity\Submission;

class UpdateInput
{
    public readonly string $submissionStrId;

    public function __construct(
        public readonly Submission $submission,
    ) {
        $this->submissionStrId = $this->submission->getStrId();
    }
}
