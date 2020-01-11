<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Data\Validator\SpeciesListValidator;
use Doctrine\ORM\EntityManagerInterface;

class FdvFactory
{
    private EntityManagerInterface $objectMgr;
    private Fixer $fixer;
    private SpeciesListValidator $speciesListValidator;

    public function __construct(
        EntityManagerInterface $objectMgr,
        Fixer $fixer,
        SpeciesListValidator $speciesListValidator
    ) {
        $this->objectMgr = $objectMgr;
        $this->fixer = $fixer;
        $this->speciesListValidator = $speciesListValidator;
    }

    public function create(Printer $printer): FixerDifferValidator
    {
        return new FixerDifferValidator($this->objectMgr, $this->fixer, $this->speciesListValidator, $printer);
    }
}
