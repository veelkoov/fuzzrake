<?php

declare(strict_types=1);

namespace App\Utils;

class ArtisanField
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $modelName;

    /**
     * @var string|null
     */
    private $validationRegexp;

    /**
     * @var bool
     */
    private $isList;

    /**
     * @var int|null
     */
    private $uiFormIndex;

    /**
     * @var bool
     */
    private $isPersisted;

    /**
     * @var bool
     */
    private $inJson;

    public function __construct(string $name, ?string $modelName, ?string $validationRegexp, bool $isList,
        bool $isPersisted, bool $inJson, ?int $uiFormIndex)
    {
        $this->name = $name;
        $this->modelName = $modelName;
        $this->validationRegexp = $validationRegexp;
        $this->isList = $isList;
        $this->uiFormIndex = $uiFormIndex;
        $this->isPersisted = $isPersisted;
        $this->inJson = $inJson;
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

    public function inJson(): bool
    {
        return $this->inJson;
    }

    public function uiFormIndex(): ?int
    {
        return $this->uiFormIndex;
    }

    public function isIncludedInUiForm(): bool
    {
        return null !== $this->uiFormIndex;
    }
}
