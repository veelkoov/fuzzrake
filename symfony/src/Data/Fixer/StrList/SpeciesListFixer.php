<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\String\GenericStringFixer;
use App\Species\SpeciesService;
use App\Utils\Regexp\Replacements;
use Override;

final class SpeciesListFixer extends AbstractListFixer
{
    private readonly Replacements $replacements;

    public function __construct(
        SpeciesService $species,
        private readonly GenericStringFixer $genericStringFixer,
    ) {
        $this->replacements = $species->getListFixerReplacements();
    }

    #[Override]
    protected function getSeparatorRegexp(): ?string
    {
        return null;
    }

    #[Override]
    protected function fixItem(string $subject): string
    {
        return $this->replacements->do($this->genericStringFixer->fix($subject));
    }
}
