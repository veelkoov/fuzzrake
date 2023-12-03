<?php

declare(strict_types=1);

namespace App\Tests\Data\Species;

use App\Data\Species\Exceptions\SharedRootSpecieException;
use App\Data\Species\SpeciesService;
use App\Repository\ArtisanRepository;
use App\Tests\TestUtils\CacheUtils;
use App\Tests\TestUtils\Cases\TestCase;
use App\Tests\TestUtils\DataDefinitions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

/**
 * @small
 */
class SpeciesServiceTest extends TestCase
{
    public function testSpeciesStatsComputations(): void
    {
        $a1 = Artisan::new()
            ->getArtisan();
        $a2 = Artisan::new()
            ->setSpeciesDoes("Wolves\nOther3")
            ->getArtisan();
        $a3 = Artisan::new() // Assumed does most species
            ->setSpeciesDoesnt('Canines')
            ->getArtisan();
        $a4 = Artisan::new()
            ->setSpeciesDoes("Real life animals\nCanines")
            ->setSpeciesDoesnt("Mammals\nOther2")
            ->getArtisan();
        $a5 = Artisan::new()
            ->setSpeciesDoes('Other4')
            ->getArtisan();

        $artisanRepositoryMock = $this->createMock(ArtisanRepository::class);
        $artisanRepositoryMock->method('getActive')->willReturn([
            $a1, $a2, $a3, $a4, $a5,
        ]);

        $speciesDefinitions = DataDefinitions::get('species.yaml', 'species_definitions');

        $subject = new SpeciesService($speciesDefinitions, $artisanRepositoryMock, CacheUtils::getArrayBased()); // @phpstan-ignore-line - Data structures
        $result = $subject->getStats();

        self::assertEquals(1, $result->unknownCount);

        $expected = [ // direct(does, doesnt, total), indirect(does, doesnt, total), total(does, doesnt, 1)
            'Most species'      => [0, 0, 0, 3, 2, 5, 3, 2, 5],
            'Real life animals' => [1, 0, 1, 2, 2, 4, 3, 2, 5],
            'Mammals'           => [0, 1, 1, 2, 1, 3, 2, 2, 4],
            'Canines'           => [1, 1, 2, 1, 0, 1, 2, 1, 3],
            'Wolves'            => [1, 0, 1, 0, 0, 0, 1, 0, 1],
            'Coyotes'           => [0, 0, 0, 0, 0, 0, 0, 0, 0],
            'Other'             => [0, 0, 0, 2, 1, 3, 2, 1, 3],
        ];

        foreach ($expected as $specieName => $specieStats) {
            $specie = $result->get($specieName);
            self::assertNotNull($specie, "$specieName is null");

            self::assertEquals($specieStats[0], $specie->directDoes, "$specieName direct does count wrong");
            self::assertEquals($specieStats[1], $specie->directDoesnt, "$specieName direct doesn't count wrong");
            self::assertEquals($specieStats[2], $specie->directTotal, "$specieName direct total count wrong");

            self::assertEquals($specieStats[3], $specie->indirectDoes, "$specieName indirect does count wrong");
            self::assertEquals($specieStats[4], $specie->indirectDoesnt, "$specieName indirect doesn't count wrong");
            self::assertEquals($specieStats[5], $specie->indirectTotal, "$specieName indirect total count wrong");

            self::assertEquals($specieStats[6], $specie->totalDoes, "$specieName total does count wrong");
            self::assertEquals($specieStats[7], $specie->totalDoesnt, "$specieName total doesn't count wrong");
            self::assertEquals($specieStats[8], $specie->total, "$specieName total count wrong");
        }
    }

    public function testSharedSpecieRootsAreNotAllowed(): void
    {
        $speciesDefinitions = [
            'valid_choices' => [
                'Most species' => [
                    'Fantasy species' => [
                        'Oops' => [],
                    ],
                ],
                'Other' => [
                    'Oops' => [],
                ],
            ],
            'replacements' => [],
            'regex_prefix' => '',
            'regex_suffix' => '',
            'leave_unchanged' => [],
        ];
        $artisanRepositoryMock = $this->createMock(ArtisanRepository::class);
        $cache = CacheUtils::getArrayBased();

        $subject = new SpeciesService($speciesDefinitions, $artisanRepositoryMock, $cache);

        $this->expectException(SharedRootSpecieException::class);
        $subject->getSpecies();
    }
}
