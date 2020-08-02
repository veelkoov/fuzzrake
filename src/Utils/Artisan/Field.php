<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class Field
{
    private string $name;
    private ?string $modelName;
    private ?string $validationRegexp;
    private bool $isList;
    private bool $isPersisted;
    private bool $public;
    private bool $inStats;

    public function __construct(string $name, ?string $modelName, ?string $validationRegexp,
        int $isList, int $isPersisted, int $inStats, int $public)
    {
        $this->name = $name;
        $this->modelName = $modelName;
        $this->validationRegexp = $validationRegexp;
        $this->isList = (bool) $isList;
        $this->isPersisted = (bool) $isPersisted;
        $this->public = (bool) $public;
        $this->inStats = (bool) $inStats;
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
