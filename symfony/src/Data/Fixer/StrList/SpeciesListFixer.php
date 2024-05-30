<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\String\GenericStringFixer;
use App\Service\SpeciesService;
use App\Utils\Regexp\Replacements;

final class SpeciesListFixer extends AbstractListFixer
{
    private readonly Replacements $replacements;

    public function __construct(
        SpeciesService $species,
        private readonly GenericStringFixer $genericStringFixer,
    ) {
        $this->replacements = $species->getListFixerReplacements();
    }

    protected function getSeparatorRegexp(): ?string
    {
        return null;
    }

    protected function fixItem(string $subject): string
    {
        return $this->replacements->do($this->genericStringFixer->fix($subject));
    }
}
