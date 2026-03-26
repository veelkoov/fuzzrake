<?php

declare(strict_types=1);

namespace App\IuHandling\Import;

use App\Data\Definitions\Fields\Field;
use App\Entity\Submission;
use App\Utils\Creator\SmartAccessDecorator as Creator;

final readonly class ImportData
{
    /**
     * @param Creator[] $matchedCreators
     * @param string[]  $errors
     */
    public function __construct(
        public Submission $submission,
        public array $matchedCreators,
        public Creator $subjectCreator,
        public Creator $inputData,
        public Creator $fixedData,
        public array $errors,
        public bool $isAccepted,
    ) {
    }

    public function submittedDifferent(Field $field): bool
    {
        return !$this->inputData->equals($field, $this->subjectCreator);
    }

    public function fixesApplied(Field $field): bool
    {
        return !$this->inputData->equals($field, $this->fixedData);
    }

    public function isChanging(Field $field): bool
    {
        return !$this->subjectCreator->equals($field, $this->fixedData);
    }

    public function isUpdate(): bool
    {
        return 1 === count($this->matchedCreators);
    }

    // TODO
    // public function getDescription(): Description
    // {
    //     return new Description($this->originalCreator, $this->updatedCreator);
    // }
}
