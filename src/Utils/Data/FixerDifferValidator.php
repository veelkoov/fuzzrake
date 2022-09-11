<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Fields;
use App\DataDefinitions\Fields\FieldsList;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use InvalidArgumentException;

class FixerDifferValidator
{
    final public const FIX = 1;
    final public const SHOW_DIFF = 2;
    final public const RESET_INVALID_PLUS_SHOW_FIX_CMD = 8;

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

            if ($resetAndShowFixCommand) {
                $artisan->getChanged()->set($field, $artisan->getSubject()->get($field));
            }
        }
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
