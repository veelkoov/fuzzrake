<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Utils\Data\Validator\SpeciesListValidator;
use Symfony\Component\Console\Style\SymfonyStyle;

class ValidatorFactory
{
    /**
     * @var SpeciesListValidator
     */
    private $speciesListValidator;

    public function __construct(array $species)
    {
        $this->speciesListValidator = new SpeciesListValidator($species);
    }

    public function create(SymfonyStyle $io): Validator
    {
        return new Validator($this->speciesListValidator, $io);
    }
}
