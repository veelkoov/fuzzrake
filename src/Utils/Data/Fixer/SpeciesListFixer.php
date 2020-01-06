<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

class SpeciesListFixer extends AbstractListFixer
{
    private const UNSPLITTABLE = [ # TODO: Improve. Should be part of import fixes file or whatever.
        'All species, but I specialize in dragons',
        'I\'d prefer not to do any pokemon/digimon, but still ask since it depends on the design',
        'I greatly prefer uncommon species and will be much more excited to work on such species. I do all, but uncommon is prefered. Examples: elephants, caracles, sheep, birds, ect.',
        'Nothing on the hard pass list, but I do not have experience with scalies or other animals without fur. I am willing to try anything!',
        'Any species that is furry! (canines, felines, fur dragons, horses, rodents, bats, etc.)',
        'Almost everything. Just ask',
        'If I find something would be too complicated to pull off, I may decline.',
        'skullsuits...', // FIXME: How the heck am i supposed to interpret that
        'None specifically that I can think of, if unsure just ask',
        'Will not do characters with antlers or overly complicated anatomy (3 mouths, 4 eyes, etc.)',
        'I won\'t take on anything that has a beak like ducks, birds, and pancans.',
        'I\'m willing to take on any species, but enjoy making monsters and creepy/crazy species most!',
        'Any and all within my capabilities of foam/fur/minky crafting. Unique or uncommon species are my soft spot.',
    ];

    /**
     * @var string[]
     */
    private $replacements;

    public function __construct(array $species)
    {
        $this->replacements = $species['replacements'];
    }

    protected static function shouldSort(): bool
    {
        return false;
    }

    protected static function getSeparatorRegexp(): string
    {
        return "#[\n,.]#";
    }

    protected static function getNonsplittable(): array
    {
        return self::UNSPLITTABLE;
    }

    protected function getReplacements(): array
    {
        return $this->replacements;
    }
}
