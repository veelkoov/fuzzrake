<?php

declare(strict_types=1);

namespace App\Tests\Data\Species;

use App\Data\Species\CreatorSpeciesResolver;
use App\Data\Species\MutableSpecie;
use App\Data\Species\MutableSpeciesList;
use App\Data\Species\SpeciesList;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

/**
 * @small
 */
class CreatorSpeciesResolverTest extends TestCase
{
    /**
     * @dataProvider getOrderedDoesDoesntDataProvider
     *
     * @param list<string> $speciesDoes
     * @param list<string> $speciesDoesnt
     */
    public function testGetOrderedDoesDoesnt(string $expected, array $speciesDoes, array $speciesDoesnt): void
    {
        $A = new MutableSpecie('A', false);
        $B = new MutableSpecie('B', false);
        $B->addParentTwoWay($A);
        $C = new MutableSpecie('C', false);
        $C->addParentTwoWay($B);
        $D = new MutableSpecie('D', false);
        $D->addParentTwoWay($C);

        $list = new MutableSpeciesList();
        $list->add($A, $B, $C, $D, new MutableSpecie('Other', false));

        $subject = new CreatorSpeciesResolver($list->toList());

        $result = $subject->getOrderedDoesDoesnt($speciesDoes, $speciesDoesnt);
        $result = implode(' ', Vec\map($result, fn (array $pair) => ($pair[1] ? '+' : '-').$pair[0]->name));

        self::assertEquals($expected, $result);
    }

    /**
     * @return list<array{string, list<string>, list<string>}>
     */
    public function getOrderedDoesDoesntDataProvider(): array
    {
        return [
            ['+A -B +C -D', ['A', 'C'], ['B', 'D']],
            ['+A -B +C -D', ['C', 'A'], ['D', 'B']],
            ['-A +B -C +D', ['B', 'D'], ['A', 'C']],
            ['-A +B -C +D', ['D', 'B'], ['C', 'A']],
        ];
    }

    /**
     * @dataProvider resolveDoesDataProvider
     */
    public function testResolveDoes(string $expected, string $speciesDoes, string $speciesDoesnt): void
    {
        $subject = new CreatorSpeciesResolver($this->getTestSpecies());

        $result = implode(', ', $subject->resolveDoes($speciesDoes, $speciesDoesnt));

        self::assertEquals($expected, $result);
    }

    /**
     * @return list<array{string, string, string}>
     */
    public function resolveDoesDataProvider(): array
    {
        return [ // expected, does, doesn't
            ['',                                              '',                   ''],
            ['Mammals, Corgis',                               "Mammals\nCorgis",    "Canines\nWith antlers"],
            ['Mammals, Canines, Wolves',                      'Mammals',            "With antlers\nDogs"],
            ['Mammals, Canines, Wolves, Deers',               "Mammals\nDeers",     "With antlers\nDogs"],
            ['With antlers, Deers, Dogs, Corgis, Dalmatians', "Dogs\nWith antlers", ''],
            ['With antlers, Dogs, Corgis, Dalmatians',        "Dogs\nWith antlers", 'Deers'],

            ['Other, Dogs, Corgis, Dalmatians', "Dogs\nPancakes", ''],
            ['Other, Dogs, Corgis',             "Dogs\nOther",    'Dalmatians'],
        ];
    }

    /**
     * Provides species list with a proper hierarchy for tests.
     *
     * - Mammals
     *   - Canines
     *     - Dogs
     *       - Corgis
     *       - Dalmatians
     *     - Wolves
     *   - Deers
     * - With antlers
     *   - Deers
     * - Other ("the Other")
     */
    private function getTestSpecies(): SpeciesList
    {
        $mammals = new MutableSpecie('Mammals', false);
        $withAntlers = new MutableSpecie('With antlers', false);

        $canines = new MutableSpecie('Canines', false);
        $canines->addParentTwoWay($mammals);

        $dogs = new MutableSpecie('Dogs', false);
        $dogs->addParentTwoWay($canines);

        $corgis = new MutableSpecie('Corgis', false);
        $corgis->addParentTwoWay($dogs);
        $dalmatians = new MutableSpecie('Dalmatians', false);
        $dalmatians->addParentTwoWay($dogs);

        $wolves = new MutableSpecie('Wolves', false);
        $wolves->addParentTwoWay($canines);

        $deers = new MutableSpecie('Deers', false);
        $deers->addParentTwoWay($mammals);
        $deers->addParentTwoWay($withAntlers);

        $result = new MutableSpeciesList();
        $result->add($mammals, ...$mammals->getDescendants());
        $result->add($withAntlers, ...$withAntlers->getDescendants());
        $result->add(new MutableSpecie('Other', false));

        return $result->toList();
    }
}
