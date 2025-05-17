<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Fields\Field;
use App\Entity\Submission;
use App\IuHandling\Changes\Description;
use App\Utils\Creator\SmartAccessDecorator as Creator;

class Update
{
    public readonly UpdateContact $contact;

    /**
     * @param Creator[] $matchedCreators
     * @param string[]  $errors
     */
    public function __construct(
        public readonly Submission $submission,
        public readonly array $matchedCreators,
        public readonly Creator $originalInput,
        public readonly Creator $originalCreator,
        public readonly Creator $updatedCreator,
        public readonly array $errors,
        public readonly bool $isAccepted,
        public readonly bool $isNew,
    ) {
        $this->contact = UpdateContact::from($this->originalCreator, $this->updatedCreator);
    }

    public function submittedDifferent(Field $field): bool
    {
        return !$this->originalInput->equals($field, $this->originalCreator);
    }

    public function fixesApplied(Field $field): bool
    {
        return !$this->originalInput->equals($field, $this->updatedCreator);
    }

    public function isChanging(Field $field): bool
    {
        return !$this->originalCreator->equals($field, $this->updatedCreator);
    }

    public function getDescription(): Description
    {
        return new Description($this->originalCreator, $this->updatedCreator);
    }
}
