<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use Stringable;
use TRegx\CleanRegex\PatternInterface;

class Field implements Stringable
{
    private ?PatternInterface $validationPattern;

    public function __construct(
        private string $name,
        private ?string $modelName,
        ?string $validationRegexp,
        private bool $isList,
        private bool $isPersisted,
        private bool $inStats,
        private bool $public,
        private bool $isInIuForm,
    ) {
        $this->validationPattern = null !== $validationRegexp ? pattern($validationRegexp) : null;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function modelName(): ?string
    {
        return $this->modelName;
    }

    public function validationPattern(): ?PatternInterface
    {
        return $this->validationPattern;
    }

    public function isList(): bool
    {
        return $this->isList;
    }

    public function isPersisted(): bool
    {
        return $this->isPersisted;
    }

    public function isValidated(): bool
    {
        return null !== $this->validationPattern;
    }

    public function isInUiForm(): bool
    {
        return $this->isInIuForm;
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
