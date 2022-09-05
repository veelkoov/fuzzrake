<?php

declare(strict_types=1);

namespace App\Submissions;

use App\DataDefinitions\Fields\Field;
use App\Entity\Submission;
use App\Submissions\Changes\Description;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class Update
{
    public readonly bool $isNew;
    public readonly bool $passwordMatches;

    /**
     * @param Artisan[] $matchedArtisans
     * @param string[]  $errors
     */
    public function __construct(
        public readonly SubmissionData $submissionData,
        public readonly Submission $submission,
        public readonly array $matchedArtisans,
        public readonly Artisan $originalInput,
        public readonly Artisan $originalArtisan,
        public readonly Artisan $updatedArtisan,
        public readonly array $errors,
    ) {
        $this->isNew = null === $this->originalArtisan->getId();
        $this->passwordMatches = $this->originalArtisan->getPassword() === $this->updatedArtisan->getPassword();
    }

    public function submittedDifferent(Field $field): bool
    {
        return !$this->originalInput->equals($field, $this->originalArtisan);
    }

    public function fixesApplied(Field $field): bool
    {
        return !$this->originalInput->equals($field, $this->updatedArtisan);
    }

    public function isChanging(Field $field): bool
    {
        return !$this->originalArtisan->equals($field, $this->updatedArtisan);
    }

    public function getDescription(): Description
    {
        return new Description($this->originalArtisan, $this->updatedArtisan);
    }
}
