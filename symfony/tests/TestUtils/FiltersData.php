<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Entity\CreatorSpecie;
use App\Entity\Specie;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\Collections\Lists;
use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

class FiltersData
{
    use UtilityClass;

    /**
     * Species and specie <-> creator relationships are created in a complex process. To mock the data for tests,
     * given test creator entities, this will return all entities required for specie-based filtering to "work".
     *
     * @param list<Creator> $creators
     *
     * @return list<Specie|CreatorSpecie>
     */
    public static function entitiesFrom(array $creators): array
    {
        self::makeSureNoCreatorUsesSpeciesDoesnt($creators);

        $species = Dict\from_keys(
            self::getSpecieNamesFrom($creators),
            fn (string $name) => (new Specie())->setName($name),
        );

        $creatorSpecies = [];

        foreach ($creators as $creator) {
            $creatorSpecies = [...$creatorSpecies, ...Vec\map($creator->getSpeciesDoes(),
                fn (string $name) => (new CreatorSpecie())
                    ->setSpecie($species[$name])
                    ->setCreator($creator->getArtisan()),
            )];
        }

        return Vec\values([...$species, ...$creatorSpecies]);
    }

    /**
     * @param list<Creator> $creators
     */
    private static function makeSureNoCreatorUsesSpeciesDoesnt(array $creators): void
    {
        if (Iter\any($creators, fn (Creator $creator) => [] !== $creator->getSpeciesDoesnt())) {
            throw new InvalidArgumentException(__CLASS__.' does not support resolving species. Creators cannot have "species doesn\'t" specified.');
        }
    }

    /**
     * @param list<Creator> $creators
     *
     * @return list<string>
     */
    private static function getSpecieNamesFrom(array $creators): array
    {
        return Lists::unique(array_merge(...Vec\map($creators, fn (Creator $creator) => $creator->getSpeciesDoes())));
    }
}
