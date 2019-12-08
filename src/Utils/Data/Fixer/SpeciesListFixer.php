<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

class SpeciesListFixer extends AbstractListFixer
{
    private const UNSPLITTABLE = [
        'All species, but I specialize in dragons',
        'I\'d prefer not to do any pokemon/digimon, but still ask since it depends on the design',
        'I greatly prefer uncommon species and will be much more excited to work on such species. I do all, but uncommon is prefered. Examples: elephants, caracles, sheep, birds, ect.',
        'Nothing on the hard pass list, but I do not have experience with scalies or other animals without fur. I am willing to try anything!',
        'Any species that is furry! (canines, felines, fur dragons, horses, rodents, bats, etc.)',
        'Almost everything. Just ask',
        'If I find something would be too complicated to pull off, I may decline.',
        'skullsuits...', // FIXME: How the heck am i supposed to interpret that
    ];

    private const REPLACEMENTS = [
        'Currently not available for reptilian or avian characters' => "Avians\nReptilians",
        'Canines, felines, mustelids, and rodents'                  => "Canines\nFelines\nMustelids\nRodents",

        'All ?!?'                           => 'Any',
        'Almost everything. Just ask'       => 'Most species',
        'Avians?'                           => 'Avians',
        'Big/small felines'                 => "Big felines\nSmall felines",
        'Big and small cats'                => "Big cats\nSmall cats",
        'Birds?'                            => 'Birds',
        'Cats?'                             => 'Cats',
        'Deers?'                            => 'Deers',
        'Dogs?'                             => 'Dogs',
        'Dragons?'                          => 'Dragons',
        'Drekkubus(es)?'                    => 'Drekkubuses',
        'Dutch Angel Dragons/Angel Dragons' => "Dutch Angel Dragons\nAngel Dragons",
        'Equines?'                          => 'Equines',
        'Fish(es)?'                         => 'Fishes',
        'Fox(es)?'                          => 'Foxes',
        'K-9\'s'                            => 'Canines',
        'Lions?'                            => 'Lions',
        'Manokits?'                         => 'Manokits',
        'Opossums?'                         => 'Opossums',
        'Primagens and protogens'           => "Primagens\nProtogens",
        'Protogens?'                        => 'Protogens',
        'Rats?'                             => 'Rats',
        'Reptiles?'                         => 'Reptiles',
        'Reptilians?'                       => 'Reptilians',
        'Ser[gi]als?'                       => 'Sergals',
        'Skull Animals?'                    => 'Skull animals',
        'Wolf'                              => 'Wolves',
        'Wickerbeasts?'                     => 'Wickerbeasts',
        'Vulpines?'                         => 'Vulpine',
    ];

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

    protected static function getReplacements(): array
    {
        return self::REPLACEMENTS;
    }
}
