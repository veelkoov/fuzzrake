<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\Fields;
use App\DataDefinitions\Fields\FieldsList;
use App\IuHandling\Import\Manager;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Console\Formatter;
use App\Utils\StrUtils;
use InvalidArgumentException;

class FixerDifferValidator
{
    final public const FIX = 1;
    final public const SHOW_DIFF = 2;
    final public const RESET_INVALID_PLUS_SHOW_FIX_CMD = 8;
    final public const SHOW_FIX_CMD_FOR_INVALID = 16;
    final public const USE_SET_FOR_FIX_CMD = 32;

    private readonly Differ $differ;

    public function __construct(
        private readonly Fixer $fixer,
        private readonly Validator $validator,
        private readonly Printer $printer,
    ) {
        $this->differ = new Differ($this->printer);
    }

    public function perform(ArtisanChanges $artisan, int $flags = 0, FieldsList $skipDiffFor = null): void
    {
        $artisan = $this->getArtisanFixWip($artisan);
        $skipDiffFor ??= Fields::none();

        foreach (Fields::persisted() as $field) {
            $this->printer->setCurrentContext($artisan);

            if ($flags & self::FIX) {
                $this->fixer->fix($artisan->getChanged(), $field);
            }

            if ($flags & self::SHOW_DIFF && !$skipDiffFor->has($field)) {
                $this->differ->showDiff($field, $artisan->getSubject(), $artisan->getChanged());
            }

            $isValid = $this->validator->isValid($artisan->getChanged(), $field);
            $resetAndShowFixCommand = $flags & self::RESET_INVALID_PLUS_SHOW_FIX_CMD && !$isValid;

            if (!$isValid && $flags & self::SHOW_FIX_CMD_FOR_INVALID || $resetAndShowFixCommand) {
                $this->printFixCommandOptionally($field, $artisan, (bool) ($flags & self::USE_SET_FOR_FIX_CMD));
            }

            if ($resetAndShowFixCommand) {
                $artisan->getChanged()->set($field, $artisan->getSubject()->get($field));
            }
        }
    }

    private function printFixCommandOptionally(Field $field, ArtisanChanges $artisan, bool $useSetForFixCmd): void
    {
        if (!$this->hideFixCommandFor($field)) {
            $originalVal = StrUtils::strSafeForCli(StrUtils::asStr($artisan->getSubject()->get($field)));

            if (!$this->validator->isValid($artisan->getChanged(), $field)) {
                $originalVal = Formatter::invalid($originalVal);
            }

            $proposedVal = StrUtils::strSafeForCli(StrUtils::asStr($artisan->getChanged()->get($field))) ?: 'NEW_VALUE';

            if ($useSetForFixCmd) {
                $fixCmd = Manager::CMD_SET." $field->name \"$proposedVal\"";
            } else {
                $fixCmd = Manager::CMD_REPLACE." $field->name \"$originalVal\" \"$proposedVal\"";
            }

            $this->printer->writeln(Formatter::fix("    $fixCmd"));
        }
    }

    private function hideFixCommandFor(Field $field): bool
    {
        return !$field->isInIuForm() || in_array($field, [
            Field::CONTACT_ADDRESS_PLAIN,
            Field::PASSWORD,
        ]);
    }

    private function getArtisanFixWip(Artisan|ArtisanChanges $artisan): ArtisanChanges
    {
        if ($artisan instanceof Artisan) {
            $artisan = new ArtisanChanges($artisan);
        } elseif (!($artisan instanceof ArtisanChanges)) {
            throw new InvalidArgumentException();
        }

        return $artisan;
    }
}
