<?php

declare(strict_types=1);

namespace App\Tests\Utils\Species;

use App\Data\Stats\SpeciesStatsService;
use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Tests\TestUtils\Cases\TestCase;
use App\Tests\TestUtils\DataDefinitions;
use App\Utils\Species\SpeciesService;

/**
 * @small
 */
class SpeciesServiceTest extends TestCase
{
    public function testNoRecursionInConfigAndStatsSmokeTest(): void
    {
        $artisan = (new Artisan())
            ->setSpeciesDoes("Ducks\nBirds")
            ->setSpeciesDoesnt("Wolves\nAaBbCcDdEeFfGgHhJj");

        $repositoryMock = $this->createMock(ArtisanRepository::class);
        $repositoryMock->method('getActive')->willReturn([$artisan]);

        $definitions = DataDefinitions::get('species.yaml', 'species_definitions');
        $speciesService = new SpeciesService($definitions); // @phpstan-ignore-line Definitions

        $subject = new SpeciesStatsService($repositoryMock, $speciesService);

        $result = $subject->getStats();
        // TODO: Nullable?
        self::assertEquals(1, $result->get('Ducks')?->total);
        self::assertEquals(2, $result->get('Birds')?->total);
        self::assertEquals(1, $result->get('Birds')?->directDoes);
        self::assertEquals(1, $result->get('Wolves')?->total);
        self::assertEquals(3, $result->get('Most species')?->total);
        self::assertEquals(1, $result->get('AaBbCcDdEeFfGgHhJj')?->total);
    }
}
