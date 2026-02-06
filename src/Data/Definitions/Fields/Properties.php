<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class Properties
{
    /**
     * @param ?non-empty-string $validationRegex
     */
    public function __construct(
        public string $modelName,
        public Type $type = Type::STRING,
        public bool $public = true,
        public bool $inIuForm = true,
        public bool $inStats = true,
        public bool $freeForm = true,
        public ?string $validationRegex = null,
        public bool $isList = false,
        public bool $persisted = true,
        public bool $affectedByIuForm = false,
        public bool $notInspectedUrl = false,
    ) {
    }
}
