<?php

declare(strict_types=1);

namespace App\Utils\Filters;

class SpecialItem
{
    private int $count = 0;

    public function __construct(
        private readonly string $idPart,
        private readonly string $value,
        private readonly string $label,
        private readonly string $faIcon,
    ) {
    }

    public function incCount(int $number = 1): void
    {
        $this->count += $number;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getIdPart(): string
    {
        return $this->idPart;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getFaIcon(): string
    {
        return $this->faIcon;
    }
}
