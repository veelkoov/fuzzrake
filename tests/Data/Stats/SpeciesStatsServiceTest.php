<?php

declare(strict_types=1);

namespace App\Tests\Data\Stats;

use App\Data\Species\SpeciesService;
use App\Data\Stats\SpeciesStatsService;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Tests\TestUtils\DataDefinitions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class SpeciesStatsServiceTest extends KernelTestCaseWithEM
{
    public function testSpeciesStatsComputations(): void
    {
        $a1u = Artisan::new();
        $a2ui = Artisan::new()->setInactiveReason('Inactive')->getArtisan();
        $a5i = Artisan::new()->setSpeciesDoes("Coyotes\nOther1")
            ->setInactiveReason('Inactive');

        $a3 = Artisan::new()->setSpeciesDoes("Wolves\nOther3");
        $a4 = Artisan::new() // Assumed does most species
            ->setSpeciesDoesnt('Canines');
        $a6 = Artisan::new()->setSpeciesDoes("Real life animals\nCanines")
            ->setSpeciesDoesnt("Mammals\nOther2");
        $a7 = Artisan::new()->setSpeciesDoes('Other4');

        self::persistAndFlush($a1u, $a2ui, $a3, $a4, $a5i, $a6, $a7);

        $speciesDefinitions = DataDefinitions::get('species.yaml', 'species_definitions');
        $speciesService = new SpeciesService($speciesDefinitions); // @phpstan-ignore-line - Data structures
        $result = (new SpeciesStatsService(self::getArtisanRepository(), $speciesService))->getStats();

        self::assertEquals(1, $result->unknownCount);

        $expected = [ // direct(does, doesnt, total), indirect(does, doesnt, total), total(does, doesnt, 1), real does
            'Most species'      => [0, 0, 0, 3, 2, 5, 3, 2, 5, 1],
            'Real life animals' => [1, 0, 1, 2, 2, 4, 3, 2, 5, 2],
            'Mammals'           => [0, 1, 1, 2, 1, 3, 2, 2, 4, 1],
            'Canines'           => [1, 1, 2, 1, 0, 1, 2, 1, 3, 1],
            'Wolves'            => [1, 0, 1, 0, 0, 0, 1, 0, 1, 2],
            'Coyotes'           => [0, 0, 0, 0, 0, 0, 0, 0, 0, 1],
            'Other'             => [0, 0, 0, 2, 1, 3, 2, 1, 3, 0],
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

            self::assertEquals($specieStats[9], $specie->realDoes, "$specieName real does count wrong");
        }
    }
}
