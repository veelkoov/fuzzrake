<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\DataDefinitions\Fields\Fields;
use App\DataDefinitions\Fields\FieldsList;

class FixerDifferValidator
{
    final public const FIX = 1;
    final public const SHOW_DIFF = 2;

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
        $skipDiffFor ??= Fields::none();

        foreach (Fields::persisted() as $field) {
            $this->printer->setCurrentContext($artisan);

            if ($flags & self::FIX) {
                $this->fixer->fix($artisan->getChanged(), $field);

                if (!$this->validator->isValid($artisan->getChanged(), $field)) {
                    $artisan->getChanged()->set($field, $artisan->getSubject()->get($field));
                }
            }

            if ($flags & self::SHOW_DIFF && !$skipDiffFor->has($field)) {
                $this->differ->showDiff($field, $artisan->getSubject(), $artisan->getChanged());
            }
        }
    }
}
