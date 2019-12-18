<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\Data\Validator\GenericValidator;
use App\Utils\Data\Validator\SpeciesListValidator;
use App\Utils\Data\Validator\ValidatorInterface;
use App\Utils\StrUtils;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class Validator
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var SpeciesListValidator
     */
    private $speciesListValidator;

    /**
     * @var GenericValidator
     */
    private $genericValidator;

    public function __construct(SpeciesListValidator $speciesListValidator, SymfonyStyle $io)
    {
        $this->speciesListValidator = $speciesListValidator;
        $this->io = $io;

        $this->io->getFormatter()->setStyle('wrong', new OutputFormatterStyle('red'));
        $this->genericValidator = new GenericValidator();
    }

    public function validate(Artisan $artisan): bool
    {
        $result = true;

        foreach (Fields::persisted() as $field) {
            if (!$this->getValidator($field)->validate($field, $artisan->get($field))) {
                $safeValue = StrUtils::strSafeForCli($artisan->get($field));

                $this->io->writeln("wr:{$artisan->getMakerId()}:{$field->name()}:|:<wrong>$safeValue</>|$safeValue|");

                $result = false;
            }
        }

        return $result;
    }

    private function getValidator(Field $field): ValidatorInterface
    {
        switch ($field->name()) {
            case Fields::SPECIES_DOES:
            case Fields::SPECIES_DOESNT:
                return $this->speciesListValidator;

            default:
                return $this->genericValidator;
        }
    }
}
