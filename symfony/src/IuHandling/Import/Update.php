<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Fields\Field;
use App\Entity\Submission;
use App\IuHandling\Changes\Description;
use App\Utils\Creator\SmartAccessDecorator as Creator;

final readonly class Update
{
    public UpdateContact $contact;

    /**
     * @param Creator[] $matchedCreators
     * @param string[]  $errors
     */
    public function __construct(
        public Submission $submission,
        public array $matchedCreators,
        public Creator $originalInput,
        public Creator $originalCreator,
        public Creator $updatedCreator,
        public array $errors,
        public bool $isAccepted,
        public bool $isNew,
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
