<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests\Filters;

use App\Filtering\DataRequests\Filters\SpeciesFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Species\Specie;
use App\Utils\Species\SpeciesList;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class SpeciesFilterTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     *
     * @param list<string> $searched
     */
    public function testMatches(string $does, string $doesnt, array $searched, bool $matched): void
    {
        $subject = new SpeciesFilter($searched, self::getSpeciesList());
        $artisan = Artisan::new()->setSpeciesDoes($does)->setSpeciesDoesnt($doesnt);

        self::assertEquals($matched, $subject->matches($artisan));
    }

    /**
     * @return list<array{string, string, list<string>, bool}>
     */
    public function matchesProvider(): array
    {
        return [ // does, doesn't, searched, matched
            ['',                 '',       [],          false],
            ['Mammals',          '',       [],          false],
            ['Mammals',          '',       ['Mammals'], true],
            ['Canines',          '',       ['Mammals'], true],
            ['Wolves',           '',       ['Mammals'], true],
            ["Canines\nFelines", 'Tigers', ['Mammals'], true],
            ["Canines\nFelines", 'Tigers', ['Canines'], true],
            ["Canines\nFelines", 'Tigers', ['Wolves'],  true],
            ["Canines\nFelines", 'Tigers', ['Tigers'],  false],
        ];
    }

    private function getSpeciesList(): SpeciesList
    {
        $result = new SpeciesList();

        $mammals = new Specie('Mammals', false);
        $canines = new Specie('Canines', false);
        $canines->addParentTwoWay($mammals);
        $wolves = new Specie('Wolves', false);
        $wolves->addParentTwoWay($canines);
        $felines = new Specie('Felines', false);
        $felines->addParentTwoWay($mammals);
        $tigers = new Specie('Tigers', false);
        $tigers->addParentTwoWay($felines);

        $result->add($mammals, $canines, $wolves, $felines, $tigers, new Specie('Other', false));

        return $result;
    }
}
