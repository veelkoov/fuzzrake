<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Entity\CreatorSpecie;
use App\Entity\KotlinData;
use App\Entity\Specie;
use App\Repository\KotlinDataRepository;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\Json;
use App\Utils\StringList;
use InvalidArgumentException;
use JsonException;
use Nette\StaticClass;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

class FiltersData
{
    use StaticClass;

    /**
     * Species, specie <-> creator relationships, and species filter data is generated in Kotlin.
     * Given test creator entities, this will return all entities required for specie-based filtering to work in tests.
     *
     * @param list<Creator> $creators
     *
     * @return list<Specie|CreatorSpecie|KotlinData>
     *
     * @throws JsonException
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
            $creatorSpecies = [...$creatorSpecies, ...Vec\map(
                StringList::unpack($creator->getSpeciesDoes()),
                fn (string $name) => (new CreatorSpecie())
                    ->setSpecie($species[$name])
                    ->setCreator($creator->getArtisan()),
            )];
        }

        $speciesFilterKotlinData = self::getSpeciesFilterKotlinData(Vec\keys($species));

        return Vec\values([...$species, ...$creatorSpecies, $speciesFilterKotlinData]);
    }

    /**
     * Resolving species done by a creator is now being done by Kotlin. This class supports only simple test cases.
     * Throw an exception if SPECIES_DOESNT got used - it should not have been.
     *
     * @param list<Creator> $creators
     */
    private static function makeSureNoCreatorUsesSpeciesDoesnt(array $creators): void
    {
        if (Iter\any($creators, fn (Creator $creator) => '' !== $creator->getSpeciesDoesnt())) {
            // Since resolving species takes place on Kotlin side, we can only test simple cases
            throw new InvalidArgumentException('Cannot test the "species doesn\'t"');
        }
    }

    /**
     * @param list<Creator> $creators
     *
     * @return list<string>
     */
    private static function getSpecieNamesFrom(array $creators): array
    {
        return array_unique(Iter\reduce(
            Vec\map($creators, fn (Creator $creator) => StringList::unpack($creator->getSpeciesDoes())),
            fn (array $c1, array $c2) => [...$c1, ...$c2],
            [],
        ));
    }

    /**
     * @param list<string> $specieNames
     *
     * @throws JsonException
     */
    private static function getSpeciesFilterKotlinData(array $specieNames): KotlinData
    {
        $subItems = [];

        foreach ($specieNames as $specieName) {
            $subItems[] = [
                        'label' => $specieName,
                        'value' => $specieName,
                        'count' => 0, // Does not matter in tests
                        'subItems' => [],
                    ];
        }

        return (new KotlinData())
            ->setName(KotlinDataRepository::SPECIES_FILTER)
            ->setJson(Json::encode([
                'items' => [
                    [
                        'label' => 'Most species',
                        'value' => 'Most species',
                        'count' => 0, // Does not matter in tests
                        'subItems' => $subItems,
                    ],
                ],
                'specialItems' => [
                    [
                        'label' => 'Unknown',
                        'value' => '?',
                        'count' => 0, // Does not matter in tests
                        'type' => 'unknown',
                    ],
                ],
            ]));
    }
}
