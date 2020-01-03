<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\Data\Validator\SpeciesListValidator;
use App\Utils\StrUtils;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class FixerDifferValidator
{
    public const FIX = 0x1;
    public const SHOW_DIFF = 0x2;
    public const SHOW_ALL_FIX_CMD = 0x4;
    public const RESET_INVALID_PLUS_SHOW_FIX_CMD = 0x8;

    private Fixer $fixer;
    private Differ $differ;
    private Validator $validator;
    private EntityManagerInterface $objectMgr;
    private Printer $printer;

    public function __construct(
        EntityManagerInterface $objectMgr,
        Fixer $fixer,
        SpeciesListValidator $speciesListValidator,
        Printer $printer
    ) {
        $this->objectMgr = $objectMgr;
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
        if ($artisan instanceof Artisan) {
            $artisan = new ArtisanFixWip($artisan, $this->objectMgr);
        } elseif (!($artisan instanceof ArtisanFixWip)) {
            throw new InvalidArgumentException();
        }

        foreach (Fields::persisted() as $field) {
            $this->printer->setCurrentContext($artisan);

            if ($flags & self::FIX) {
                $this->fixer->fix($artisan->getFixed(), $field);
            }

            if ($flags & self::SHOW_DIFF) {
                $this->differ->showDiff($field, $artisan->getOriginal(), $artisan->getFixed(), $imported);
            }

            $needsReset = $flags & self::RESET_INVALID_PLUS_SHOW_FIX_CMD && !$this->validator->isValid($artisan, $field);

            if ($flags & self::SHOW_ALL_FIX_CMD || $needsReset) {
                $this->printFixCommandOptionally($field, $artisan);
            }

            if ($needsReset) {
                $artisan->reset($field);
            }
        }

        return $artisan;
    }

    private function printFixCommandOptionally(Field $field, ArtisanFixWip $artisan): void
    {
        if (!$this->hideFixCommandFor($field)) {
            $fieldName = $field->name();
            $makerId = $artisan->getFixed()->getMakerId();
            $proposedVal = StrUtils::strSafeForCli($artisan->getFixed()->get($field)) ?: 'NEW_VALUE';
            $originalVal = Printer::formatInvalid(StrUtils::strSafeForCli($artisan->getOriginal()->get($field)));

            $this->printer->writeln("wr:$makerId:$fieldName:|:$originalVal|$proposedVal|");
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
