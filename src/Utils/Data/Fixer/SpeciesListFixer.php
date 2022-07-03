<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Replacements;
use App\Utils\Species\SpeciesService;

class SpeciesListFixer extends AbstractListFixer
{
    /**
     * @var string[]
     */
    private readonly array $unsplittable;

    private readonly Replacements $replacements;

    /**
     * @param psFixerConfig $strings
     * @param psFixerConfig $lists
     */
    public function __construct(SpeciesService $species, array $strings, array $lists)
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

    protected function getNonsplittable(string $subject): array
    {
        return $this->unsplittable;
    }

    protected function getReplacements(): Replacements
    {
        return $this->replacements;
    }
}
