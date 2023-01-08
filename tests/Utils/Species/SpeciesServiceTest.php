<?php

declare(strict_types=1);

namespace App\Tests\Utils\Species;

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

        $subject = new SpeciesService($definitions, $repositoryMock); // @phpstan-ignore-line Definitions

        $result = $subject->getStats();
        self::assertEquals(1, $result['Ducks']->getTotalCount());
        self::assertEquals(2, $result['Birds']->getTotalCount());
        self::assertEquals(1, $result['Birds']->getDirectDoesCount());
        self::assertEquals(1, $result['Wolves']->getTotalCount());
        self::assertEquals(3, $result['Most species']->getTotalCount());
        self::assertEquals(1, $result['AaBbCcDdEeFfGgHhJj']->getTotalCount());
    }
}
