<?php

declare(strict_types=1);

namespace App\Utils\Filters;

use App\Utils\Enforce;
use InvalidArgumentException;

class Item
{
    private readonly string $label;

    public function __construct(
        private readonly string|Set $value,
        string $label = '',
        private int $count = 0,
    ) {
        if ('' === $label) {
            if (!is_string($value)) {
                throw new InvalidArgumentException('Label required for non-string items');
            }

            $label = $value;
        }

        $this->label = $label;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getValue(): string|Set
    {
        return $this->value;
    }

    public function getValueString(): string
    {
        return Enforce::string($this->value);
    }

    public function getValueSet(): Set
    {
        return Enforce::objectOf($this->value, Set::class);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function incCount(int $number = 1): void
    {
        $this->count += $number;
    }

    public function isComplex(): bool
    {
        return $this->value instanceof Set;
    }
}
