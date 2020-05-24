<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\Data\Validator\SpeciesListValidator;
use App\Utils\DataInput\Manager;
use App\Utils\StrUtils;
use InvalidArgumentException;

class FixerDifferValidator
{
    public const FIX = 1;
    public const SHOW_DIFF = 2;
    public const SHOW_ALL_FIX_CMD_FOR_CHANGED = 4;
    public const RESET_INVALID_PLUS_SHOW_FIX_CMD = 8;
    public const SHOW_FIX_CMD_FOR_INVALID = 16;

    private Fixer $fixer;
    private Differ $differ;
    private Validator $validator;
    private Printer $printer;

    public function __construct(Fixer $fixer, SpeciesListValidator $speciesListValidator, Printer $printer)
    {
        $this->fixer = $fixer;
        $this->printer = $printer;

        $this->differ = new Differ($this->printer);
        $this->validator = new Validator($speciesListValidator);
    }

    /**
     * @param Artisan|ArtisanFixWip $artisan
     */
    public function perform($artisan, int $flags = 0, Artisan $imported = null): ArtisanFixWip
    {
        $artisan = $this->getArtisanFixWip($artisan);
        $anyDifference = $this->hasAnyDifference($artisan);

        foreach (Fields::persisted() as $field) {
            $this->printer->setCurrentContext($artisan);

            if ($flags & self::FIX) {
                $this->fixer->fix($artisan->getFixed(), $field);
            }

            if ($flags & self::SHOW_DIFF) {
                $this->differ->showDiff($field, $artisan->getOriginal(), $artisan->getFixed(), $imported);
            }

            $isValid = $this->validator->isValid($artisan, $field);
            $resetAndShowFixCommand = $flags & self::RESET_INVALID_PLUS_SHOW_FIX_CMD && !$isValid;

            if ($anyDifference && $flags & self::SHOW_ALL_FIX_CMD_FOR_CHANGED
                || !$isValid && $flags & self::SHOW_FIX_CMD_FOR_INVALID
                || $resetAndShowFixCommand) {
                $this->printFixCommandOptionally($field, $artisan, $imported);
            }

            if ($resetAndShowFixCommand) {
                $artisan->getFixed()->set($field, $artisan->getOriginal()->get($field));
            }
        }

        return $artisan;
    }

    private function printFixCommandOptionally(Field $field, ArtisanFixWip $artisan, ?Artisan $imported): void
    {
        if (!$this->hideFixCommandFor($field)) {
            $makerId = $artisan->getFixed()->getMakerId();
            $fieldName = $field->name();

            $original = $imported ?? $artisan->getOriginal();
            $originalVal = StrUtils::strSafeForCli($original->get($field));
            if (!$this->validator->isValid($artisan, $field)) {
                $originalVal = Printer::formatInvalid($originalVal);
            }

            $proposedVal = StrUtils::strSafeForCli($artisan->getFixed()->get($field)) ?: 'NEW_VALUE';

            $this->printer->writeln(Printer::formatFix(Manager::CMD_REPLACE.":$makerId:$fieldName:|:$originalVal|$proposedVal|"));
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

    /**
     * @param Artisan|ArtisanFixWip $artisan
     */
    private function getArtisanFixWip($artisan): ArtisanFixWip
    {
        if ($artisan instanceof Artisan) {
            $artisan = new ArtisanFixWip($artisan);
        } elseif (!($artisan instanceof ArtisanFixWip)) {
            throw new InvalidArgumentException();
        }

        return $artisan;
    }

    private function hasAnyDifference(ArtisanFixWip $artisan): bool
    {
        foreach (Fields::persisted() as $field) {
            if ($artisan->getOriginal()->get($field) != $artisan->getFixed()->get($field)) {
                return true;
            }
        }

        return false;
    }
}
