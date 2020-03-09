<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Service\Species;

class SpeciesListFixer extends AbstractListFixer
{
    private Species $species;

    public function __construct(Species $species)
    {
        $this->species = $species;
    }

    protected static function shouldSort(): bool
    {
        return false;
    }

    protected static function getSeparatorRegexp(): string
    {
        return "#[\n,.]#";
    }

    protected function getNonsplittable(): array
    {
        return $this->species->getNonsplittable();
    }

    protected function getReplacements(): array
    {
        return $this->species->getReplacements();
    }
}
