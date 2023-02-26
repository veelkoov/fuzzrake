<?php

declare(strict_types=1);

namespace App\Tests\Data\Species;

use App\Data\Species\CreatorSpeciesResolver;
use App\Utils\Species\Specie;
use App\Utils\Species\SpeciesList;
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
        $A = new Specie('A', false);
        $B = new Specie('B', false);
        $B->addParentTwoWay($A);
        $C = new Specie('C', false);
        $C->addParentTwoWay($B);
        $D = new Specie('D', false);
        $D->addParentTwoWay($C);

        $list = new SpeciesList();
        $list->add($A, $B, $C, $D, new Specie('Other', false));

        $subject = new CreatorSpeciesResolver($list);

        $result = $subject->getOrderedDoesDoesnt($speciesDoes, $speciesDoesnt);
        $result = implode(' ', Vec\map($result, fn (array $pair) => ($pair[1] ? '+' : '-').$pair[0]->getName()));

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
        $mammals = new Specie('Mammals', false);
        $withAntlers = new Specie('With antlers', false);

        $canines = new Specie('Canines', false);
        $canines->addParentTwoWay($mammals);

        $dogs = new Specie('Dogs', false);
        $dogs->addParentTwoWay($canines);

        $corgis = new Specie('Corgis', false);
        $corgis->addParentTwoWay($dogs);
        $dalmatians = new Specie('Dalmatians', false);
        $dalmatians->addParentTwoWay($dogs);

        $wolves = new Specie('Wolves', false);
        $wolves->addParentTwoWay($canines);

        $deers = new Specie('Deers', false);
        $deers->addParentTwoWay($mammals);
        $deers->addParentTwoWay($withAntlers);

        $result = new SpeciesList();
        $result->add($mammals, ...$mammals->getDescendants());
        $result->add($withAntlers, ...$withAntlers->getDescendants());
        $result->add(new Specie('Other', false));

        return $result;
    }
}
