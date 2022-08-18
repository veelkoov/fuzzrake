<?php

declare(strict_types=1);

namespace App\Submissions;

use App\DataDefinitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\IuSubmissions\IuSubmission;

class Update
{
    /**
     * @param Artisan[] $matchedArtisans
     */
    public function __construct(
        public readonly IuSubmission $submission,
        public readonly array $matchedArtisans,
        public readonly Artisan $originalInput,
        public readonly Artisan $originalArtisan,
        public readonly Artisan $updatedArtisan,
    ) {
    }

    public function isNew(): bool
    {
        return null === $this->originalArtisan->getId();
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
}
