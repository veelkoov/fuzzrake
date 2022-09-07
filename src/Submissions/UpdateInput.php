<?php

declare(strict_types=1);

namespace App\Submissions;

use App\Entity\Submission;

class UpdateInput
{
    public readonly string $submissionStrId;

    public function __construct(
        public readonly SubmissionData $submissionData,
        public readonly Submission $submission,
    ) {
        $this->submissionStrId = $this->submissionData->getId();
    }
}
