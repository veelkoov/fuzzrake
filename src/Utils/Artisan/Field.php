<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use Stringable;

class Field implements Stringable
{
    public function __construct(
        private string $name,
        private ?string $modelName,
        private ?string $validationRegexp,
        private bool $isList,
        private bool $isPersisted,
        private bool $inStats,
        private bool $public,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function modelName(): ?string
    {
        return $this->modelName;
    }

    public function validationRegexp(): ?string
    {
        return $this->validationRegexp;
    }

    public function isList(): bool
    {
        return $this->isList;
    }

    public function isPersisted(): bool
    {
        return $this->isPersisted;
    }

    public function public(): bool
    {
        return $this->public;
    }

    public function inStats(): bool
    {
        return $this->inStats;
    }

    public function is(string $name): bool
    {
        return $this->name === $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
