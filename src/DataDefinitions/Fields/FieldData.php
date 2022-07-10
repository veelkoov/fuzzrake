<?php

declare(strict_types=1);

namespace App\DataDefinitions\Fields;

use TRegx\CleanRegex\Pattern;

class FieldData
{
    public readonly ?Pattern $validationPattern;
    public readonly bool $isValidated;

    public function __construct(
        public readonly string $name,
        public readonly string $modelName,
        ?string $validationRegexp,
        public readonly bool $isList,
        public readonly bool $isPersisted,
        public readonly bool $inStats,
        public readonly bool $public,
        public readonly bool $isInIuForm,
    ) {
        $this->validationPattern = null !== $validationRegexp ? pattern($validationRegexp) : null;
        $this->isValidated = null !== $this->validationPattern;
    }
}
