<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Data\Fixer\SpeciesListFixer;
use App\Utils\Data\Validator\SpeciesListValidator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Style\SymfonyStyle;

class FdvFactory
{
    /**
     * @var ObjectManager
     */
    private $objectMgr;

    /**
     * @var SpeciesListFixer
     */
    private $speciesListFixer;

    /**
     * @var SpeciesListValidator
     */
    private $speciesListValidator;

    public function __construct(
        ObjectManager $objectMgr,
        SpeciesListFixer $speciesListFixer,
        SpeciesListValidator $speciesListValidator
    ) {
        $this->objectMgr = $objectMgr;
        $this->speciesListFixer = $speciesListFixer;
        $this->speciesListValidator = $speciesListValidator;
    }

    public function create(SymfonyStyle $io): FixerDifferValidator
    {
        return new FixerDifferValidator($this->objectMgr, $this->speciesListFixer, $this->speciesListValidator, $io);
    }
}
