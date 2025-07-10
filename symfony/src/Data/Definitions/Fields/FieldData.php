<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

readonly class FieldData
{
    public bool $isValidated;

    /**
     * @param ?non-empty-string $validationPattern
     */
    public function __construct(
        public string $name,
        public string $modelName,
        public Type $type,
        public ?string $validationPattern,
        public bool $isFreeForm,
        public bool $inStats,
        public bool $public,
        public bool $isInIuForm,
        public bool $isPersisted,
        public bool $affectedByIuForm,
        public bool $notInspectedUrl,
    ) {
        $this->isValidated = null !== $this->validationPattern;
    }
}
