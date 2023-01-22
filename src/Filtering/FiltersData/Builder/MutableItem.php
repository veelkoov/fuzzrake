<?php

declare(strict_types=1);

namespace App\Filtering\FiltersData\Builder;

use App\Utils\Enforce;
use InvalidArgumentException;

class MutableItem
{
    public readonly string $label;

    public function __construct(
        public readonly string|MutableSet $value,
        string $label = '',
        private ?int $count = 0, // TODO: #76 Species count, should not be nullable
    ) {
        if ('' === $label) {
            if (!is_string($value)) {
                throw new InvalidArgumentException('Label required for non-string items');
            }

            $label = $value;
        }

        $this->label = $label;
    }

    public function getValueSet(): MutableSet
    {
        return Enforce::objectOf($this->value, MutableSet::class);
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function incCount(?int $number = 1): void
    {
        if (null !== $this->count && null !== $number) {
            $this->count += $number;
        }
    }
}
