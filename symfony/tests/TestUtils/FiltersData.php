<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Entity\CreatorSpecie;
use App\Entity\Specie;
use App\Utils\Collections\Lists;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;
use Veelkoov\Debris\Base\DStringMap;

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

        $species = DStringMap::mapFrom(
            self::getSpecieNamesFrom($creators),
            static fn (string $name) => [$name, new Specie()->setName($name)],
        );

        $creatorSpecies = [];

        foreach ($creators as $creator) {
            $creatorSpecies = [...$creatorSpecies, ...arr_map($creator->getSpeciesDoes(),
                static fn (string $name) => new CreatorSpecie()
                    ->setSpecie($species->get($name))
                    ->setCreator($creator->entity),
            )];
        }

        return array_values([...$species, ...$creatorSpecies]);
    }

    /**
     * @param list<Creator> $creators
     */
    private static function makeSureNoCreatorUsesSpeciesDoesnt(array $creators): void
    {
        if (array_any($creators, static fn (Creator $creator) => [] !== $creator->getSpeciesDoesnt())) {
            throw new InvalidArgumentException(self::class.' does not support resolving species. Creators cannot have "species doesn\'t" specified.');
        }
    }

    /**
     * @param list<Creator> $creators
     *
     * @return list<string>
     */
    private static function getSpecieNamesFrom(array $creators): array
    {
        return Lists::unique(array_merge(...arr_map($creators, static fn (Creator $creator) => $creator->getSpeciesDoes())));
    }
}
