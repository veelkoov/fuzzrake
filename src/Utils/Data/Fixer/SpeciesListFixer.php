<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Replacements;
use App\Utils\Species\Species;

class SpeciesListFixer extends AbstractListFixer
{
    /**
     * @var string[]
     */
    private readonly array $unsplittable;

    private readonly Replacements $replacements;

    public function __construct(Species $species, array $strings, array $lists)
    {
        parent::__construct($lists, $strings);

        $this->replacements = $species->getListFixerReplacements();
        $this->unsplittable = $species->getListFixerUnsplittable();
    }

    protected static function shouldSort(): bool
    {
        return false;
    }

    protected static function getSeparatorRegexp(): string
    {
        return "[\n,.]";
    }

    protected function getNonsplittable(): array
    {
        return $this->unsplittable;
    }

    protected function getReplacements(): Replacements
    {
        return $this->replacements;
    }
}
