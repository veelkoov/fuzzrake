<?php

declare(strict_types=1);

namespace App\Submissions;

use App\DataDefinitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\IuSubmissions\IuSubmission;
use App\Utils\StringList;

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
        return $this->originalInput->get($field) !== $this->originalArtisan->get($field);
    }

    public function fixesApplied(Field $field): bool
    {
        if ($field->isList()) {
            return !StringList::sameElements($this->originalInput->getString($field), $this->updatedArtisan->getString($field));
        } else {
            return $this->originalInput->get($field) !== $this->updatedArtisan->get($field);
        }
    }

    public function isChanging(Field $field): bool
    {
        return $this->originalArtisan->get($field) !== $this->updatedArtisan->get($field);
    }
}
