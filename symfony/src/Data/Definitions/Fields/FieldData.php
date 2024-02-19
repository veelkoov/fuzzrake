<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use TRegx\CleanRegex\Pattern;

class FieldData
{
    private ?Pattern $validationPattern = null;
    public readonly bool $isValidated;

    public function __construct(
        public readonly string $name,
        public readonly string $modelName,
        public readonly Type $type,
        private readonly ?string $validationRegexp,
        public readonly bool $isFreeForm,
        public readonly bool $inStats,
        public readonly bool $public,
        public readonly bool $isInIuForm,
        public readonly bool $isPersisted,
        public readonly bool $affectedByIuForm,
        public readonly bool $notInspectedUrl,
    ) {
        $this->isValidated = null !== $this->validationRegexp;
    }

    public function getValidationPattern(): ?Pattern
    {
        return null === $this->validationRegexp ? null : ($this->validationPattern ??= pattern($this->validationRegexp, 'n'));
    }
}
