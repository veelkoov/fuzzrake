<?php

declare(strict_types=1);

namespace App\DataDefinitions\Fields;

use Attribute;

#[Attribute(Attribute::TARGET_ALL)]
readonly class Properties // TODO: Not repeatable
{
    public function __construct(
        public string $modelName,
        public bool   $public = true,
        public bool   $inIuForm = true,
        public bool   $inStats = true,
        public bool   $freeForm = true,
        public ?string $validationRegex = '',
        public bool   $isList = false,
        public bool   $dynamic = false,
        public bool   $date = false,
        public bool $affectedByIuForm = false,
        public bool $notInspectedUrl = false,
    ) {
    }
}