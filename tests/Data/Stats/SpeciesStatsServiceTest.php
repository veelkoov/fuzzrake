<?php

declare(strict_types=1);

namespace App\Tests\Data\Stats;

use App\Data\Stats\SpeciesStatsService;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Tests\TestUtils\DataDefinitions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Species\SpeciesService;

class SpeciesStatsServiceTest extends KernelTestCaseWithEM
{
    public function testSpeciesStatsComputations(): void
    {
        $a1u = Artisan::new();
        $a2ui = Artisan::new()->setInactiveReason('Inactive')->getArtisan();

        $a3 = Artisan::new()->setSpeciesDoes('Wolves');
        $a4 = Artisan::new()->setSpeciesDoesnt('Canines');
        $a5i = Artisan::new()->setSpeciesDoes('Coyotes')
            ->setInactiveReason('Inactive');
        $a6 = Artisan::new()->setSpeciesDoes("Real life animals\nCanines")
            ->setSpeciesDoesnt('Mammals');

        self::persistAndFlush($a1u, $a2ui, $a3, $a4, $a5i, $a6);

        $speciesDefinitions = DataDefinitions::get('species.yaml', 'species_definitions');
        $speciesService = new SpeciesService($speciesDefinitions); // @phpstan-ignore-line - Data structures
        $result = (new SpeciesStatsService(self::getArtisanRepository(), $speciesService))->getStats();

        self::assertEquals(1, $result->unknownCount);

        $expected = [ // direct(does doesnt total) indirect(does doesnt total) total(does doesnt 1)
            'Wolves'            => [1, 0, 1, 0, 0, 0, 1, 0, 1],
            'Canines'           => [1, 1, 2, 1, 0, 1, 2, 1, 3],
            'Mammals'           => [0, 1, 1, 2, 1, 3, 2, 2, 4],
            'Real life animals' => [1, 0, 1, 2, 2, 4, 3, 2, 5],
            'Coyotes'           => [0, 0, 0, 0, 0, 0, 0, 0, 0],
            'Most species'      => [0, 0, 0, 3, 2, 5, 3, 2, 5],
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

        // TODO: Other
        // TODO: Actual
    }
}
