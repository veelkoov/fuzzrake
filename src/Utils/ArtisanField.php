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

    /**
     * @var bool
     */
    private $inStats;

    /**
     * @var string|null
     */
    private $iuFormRegexp;

    /**
     * @var bool
     */
    private $importFromIuForm;

    /**
     * @var bool
     */
    private $exportToIuForm;

    public function __construct(string $name, ?string $modelName, ?string $validationRegexp,
        int $isList, int $isPersisted, int $inStats, int $inJson,
        ?int $uiFormIndex, ?string $iuFormRegexp, bool $importFromIuForm, bool $exportToIuForm)
    {
        $this->name = $name;
        $this->modelName = $modelName;
        $this->validationRegexp = $validationRegexp;
        $this->isList = (bool) $isList;
        $this->isPersisted = (bool) $isPersisted;
        $this->inJson = (bool) $inJson;
        $this->inStats = (bool) $inStats;
        $this->uiFormIndex = $uiFormIndex;
        $this->iuFormRegexp = $iuFormRegexp;
        $this->importFromIuForm = $importFromIuForm;
        $this->exportToIuForm = $exportToIuForm;
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

    public function inStats(): bool
    {
        return $this->inStats;
    }

    public function uiFormIndex(): ?int
    {
        return $this->uiFormIndex;
    }

    public function inIuForm(): bool
    {
        return null !== $this->uiFormIndex;
    }

    public function iuFormRegexp(): ?string
    {
        return $this->iuFormRegexp;
    }

    public function is(string $name): bool
    {
        return $this->name === $name;
    }

    public function importFromIuForm(): bool
    {
        return $this->importFromIuForm;
    }

    public function exportToIuForm(): bool
    {
        return $this->exportToIuForm;
    }
}
