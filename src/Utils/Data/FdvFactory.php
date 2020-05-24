<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Data\Validator\SpeciesListValidator;

class FdvFactory
{
    private Fixer $fixer;
    private SpeciesListValidator $speciesListValidator;

    public function __construct(Fixer $fixer, SpeciesListValidator $speciesListValidator)
    {
        $this->fixer = $fixer;
        $this->speciesListValidator = $speciesListValidator;
    }

    public function create(Printer $printer): FixerDifferValidator
    {
        return new FixerDifferValidator($this->fixer, $this->speciesListValidator, $printer);
    }
}
