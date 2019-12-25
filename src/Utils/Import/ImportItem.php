<?php

declare(strict_types=1);

namespace App\Utils\Import;

use App\Entity\Artisan;
use App\Utils\StrUtils;

class ImportItem
{
    /**
     * @var RawImportItem
     */
    private $raw;

    /**
     * @var Artisan
     */
    private $input;

    /**
     * @var Artisan
     */
    private $fixedInput;

    /**
     * @var Artisan
     */
    private $artisan;

    /**
     * @var Artisan
     */
    private $originalArtisan;

    public function __construct(RawImportItem $raw, Artisan $input, Artisan $fixedInput, Artisan $originalArtisan, Artisan $artisan)
    {
        $this->raw = $raw;
        $this->input = $input;
        $this->fixedInput = $fixedInput;
        $this->originalArtisan = $originalArtisan;
        $this->artisan = $artisan;
    }

    public function getInput(): Artisan
    {
        return $this->input;
    }

    public function getFixedInput(): Artisan
    {
        return $this->fixedInput;
    }

    public function getArtisan(): Artisan
    {
        return $this->artisan;
    }

    public function getOriginalArtisan(): Artisan
    {
        return $this->originalArtisan;
    }

    public function getIdStringSafe(): string
    {
        return StrUtils::artisanNamesSafeForCli($this->getInput(), $this->getArtisan(), $this->getOriginalArtisan())
            .' ['.$this->raw->getTimestamp()->format(DATE_ISO8601).']';
    }

    public function getNames(): string
    {
        return StrUtils::artisanNamesSafeForCli($this->getOriginalArtisan(), $this->getArtisan());
    }

    public function getMakerId(): string
    {
        return $this->artisan->getMakerId();
    }

    public function getHash(): string
    {
        return $this->raw->getHash();
    }

    public function getProvidedPasscode(): string
    {
        return $this->fixedInput->getPasscode();
    }
}
