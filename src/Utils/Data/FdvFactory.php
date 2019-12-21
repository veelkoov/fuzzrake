<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Data\Validator\SpeciesListValidator;
use Doctrine\Common\Persistence\ObjectManager;

class FdvFactory
{
    /**
     * @var ObjectManager
     */
    private $objectMgr;

    /**
     * @var Fixer
     */
    private $fixer;

    /**
     * @var SpeciesListValidator
     */
    private $speciesListValidator;

    public function __construct(
        ObjectManager $objectMgr,
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
