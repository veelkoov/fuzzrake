<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Species\SpeciesService;

class SpeciesListFixer extends AbstractListFixer
{
    private SpeciesService $species;

    public function __construct(SpeciesService $species)
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
