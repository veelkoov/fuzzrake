<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData;

use App\Filtering\FiltersData\Builder\MutableSpecialItem;

readonly class SpecialItem
{
    public function __construct(
        public string $value,
        public string $label,
        public string $faIcon,
        public int $count,
    ) {
    }

    public static function from(MutableSpecialItem $source): self
    {
        return new self(
            $source->value,
            $source->label,
            $source->faIcon,
            $source->getCount(),
        );
    }
}
