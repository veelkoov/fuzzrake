<?php

declare(strict_types=1);

namespace App\Utils\Data;

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

    public function resetInvalidFields(FixedArtisan $artisan, bool $showFixCommands): void
    {
        foreach (Fields::persisted() as $field) {
            if (!$this->getValidator($field)->validate($field, $artisan->getFixed()->get($field))) {
                if ($showFixCommands) {
                    $this->printFixCommandOptionally($field, $artisan);
                }

                $artisan->reset($field);
            }
        }
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

    private function printFixCommandOptionally(Field $field, FixedArtisan $artisan): void
    {
        if (!$this->hideFixCommandFor($field)) {
            $fieldName = $field->name();
            $makerId = $artisan->getFixed()->getMakerId();
            $proposedVal = StrUtils::strSafeForCli($artisan->getFixed()->get($field));
            $originalVal = StrUtils::strSafeForCli($artisan->getOriginal()->get($field));

            $this->io->writeln("wr:$makerId:$fieldName:|:<wrong>$originalVal</>|$proposedVal|");
        }
    }

    private function hideFixCommandFor(Field $field): bool
    {
        return in_array($field->name(), [
            Fields::CONTACT_ALLOWED,
            Fields::CONTACT_METHOD,
            Fields::CONTACT_INFO_OBFUSCATED,
            Fields::CONTACT_ADDRESS_PLAIN,
        ]);
    }
}
