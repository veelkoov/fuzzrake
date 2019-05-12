<?php

declare(strict_types=1);

namespace App\Utils;

class ArtisanField
{
    private $name;
    private $modelName;
    private $validationRegexp;
    private $isList;
    private $uiFormIndex;

    public function __construct(string $name, ?string $modelName, ?string $validationRegexp, ?bool $isList, ?int $uiFormIndex)
    {
        $this->name = $name;
        $this->modelName = $modelName;
        $this->validationRegexp = $validationRegexp;
        $this->isList = $isList;
        $this->uiFormIndex = $uiFormIndex;
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

    public function isList(): ?bool
    {
        return $this->isList;
    }

    public function uiFormIndex(): ?int
    {
        return $this->uiFormIndex;
    }

    public function isIncludedInUiForm(): bool
    {
        return null !== $this->uiFormIndex;
    }

    public function isPersisted(): bool
    {
        return null !== $this->modelName;
    }
}
