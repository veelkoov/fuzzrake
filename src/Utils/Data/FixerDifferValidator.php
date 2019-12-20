<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Data\Fixer\SpeciesListFixer;
use App\Utils\Data\Validator\SpeciesListValidator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixerDifferValidator
{
    /**
     * @var Fixer
     */
    private $fixer;

    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var Validator
     */
    private $validator;

    public function __construct(
        ObjectManager $objectMgr,
        SpeciesListFixer $speciesListFixer,
        SpeciesListValidator $speciesListValidator,
        SymfonyStyle $io
    ) {
        $this->fixer = new Fixer($objectMgr, $speciesListFixer);
        $this->differ = new Differ($io);
        $this->validator = new Validator($speciesListValidator, $io);
    }

    public function showDiffFixed(FixedArtisan $artisan)
    {
        $this->differ->showDiffFixed($artisan);
    }

    public function resetInvalidFields(FixedArtisan $artisan, bool $showFixCommands)
    {
        $this->validator->resetInvalidFields($artisan, $showFixCommands);
    }
}
