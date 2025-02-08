<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Entity\CreatorSpecie;
use App\Entity\KotlinData;
use App\Entity\Specie;
use App\Repository\KotlinDataRepository;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\Collections\Lists;
use App\Utils\Json;
use App\Utils\Traits\UtilityClass;
use InvalidArgumentException;
use JsonException;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;
use RuntimeException;

class FiltersData
{
    use UtilityClass;

    public static function getMockSpecies(): KotlinData
    {
        return self::getSpeciesFilterKotlinData([]);
    }

    /**
     * Species, specie <-> creator relationships, and species filter data is generated in Kotlin.
     * Given test creator entities, this will return all entities required for specie-based filtering to work in tests.
     *
     * @param list<Creator> $creators
     *
     * @return list<Specie|CreatorSpecie|KotlinData>
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
        if (Iter\any($creators, fn (Creator $creator) => [] !== $creator->getSpeciesDoesnt())) {
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
        return Lists::unique(array_merge(...Vec\map($creators, fn (Creator $creator) => $creator->getSpeciesDoes())));
    }

    /**
     * @param list<string> $specieNames
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

        try {
            $data = Json::encode([
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
            ]);
        } catch (JsonException $exception) {
            throw new RuntimeException(previous: $exception);
        }

        return (new KotlinData())
            ->setName(KotlinDataRepository::SPECIES_FILTER)
            ->setData($data);
    }
}
