<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

class SpeciesListFixer extends AbstractListFixer
{
    /**
     * @var string[]
     */
    private array $unsplittable;

    /**
     * @var string[]
     */
    private array $replacements;

    public function __construct(array $species, array $strings, array $lists)
    {
        parent::__construct($lists, $strings);

        $this->replacements = $species['replacements'];
        $this->unsplittable = $species['leave_unchanged'];
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
        return $this->unsplittable;
    }

    protected function getReplacements(): array
    {
        return $this->replacements;
    }
}
