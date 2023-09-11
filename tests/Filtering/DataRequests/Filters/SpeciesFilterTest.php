<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests\Filters;

use App\Data\Species\MutableSpecie;
use App\Data\Species\MutableSpeciesList;
use App\Data\Species\SpeciesList;
use App\Filtering\DataRequests\Filters\SpeciesFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

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

    public function matchesProvider(): DataProvider
    {
        return DataProvider::tuples( // does, doesn't, searched, matched
            ['',                 '',       [],          false],
            ['Mammals',          '',       [],          false],
            ['Mammals',          '',       ['Mammals'], true],
            ['Canines',          '',       ['Mammals'], true],
            ['Wolves',           '',       ['Mammals'], true],
            ['Tigers',           '',       ['Wolves'],  false],
            ["Canines\nFelines", 'Tigers', ['Mammals'], true],
            ["Canines\nFelines", 'Tigers', ['Canines'], true],
            ["Canines\nFelines", 'Tigers', ['Wolves'],  true],

            ["Canines\nFelines", 'Tigers', ['Tigers'],  false],
            ["Canines\nFelines", 'Tigers', ['Felines'], true],

            ['Tigers', '',                   ['Other'], false],
            ["Tigers\nOther", '',            ['Other'], true],
            ["Tigers\nOtherXYZ", '',         ['Other'], true],

            ['Tigers', 'Other',              ['Other'], false],
            ['Tigers', 'OtherXYZ',           ['Other'], false],
            ["Tigers\nOtherABC", 'OtherXYZ', ['Other'], true],
        );
    }

    private function getSpeciesList(): SpeciesList
    {
        $result = new MutableSpeciesList();

        $mammals = new MutableSpecie('Mammals', false);
        $canines = new MutableSpecie('Canines', false);
        $canines->addParentTwoWay($mammals);
        $wolves = new MutableSpecie('Wolves', false);
        $wolves->addParentTwoWay($canines);
        $felines = new MutableSpecie('Felines', false);
        $felines->addParentTwoWay($mammals);
        $tigers = new MutableSpecie('Tigers', false);
        $tigers->addParentTwoWay($felines);

        $result->add($mammals, $canines, $wolves, $felines, $tigers, new MutableSpecie('Other', false));

        return $result->toList();
    }
}
