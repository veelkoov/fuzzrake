<?php

declare(strict_types=1);

namespace App\Tests\Data\Species;

use App\Data\Species\SpeciesBuilder;
use App\Data\Species\SpeciesList;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

/**
 * @small
 */
class SpeciesBuilderTest extends TestCase
{
    /**
     * @param array<string, psSubspecies> $species
     *
     * @dataProvider validNamesDoesntContainDuplicatesDataProvider
     */
    public function testValidNamesDoesntContainDuplicates(array $species, int $expectedCount): void
    {
        $result = SpeciesBuilder::for($species)->list->getNames();

        self::assertCount($expectedCount, $result);
    }

    /**
     * @return list<array{array<string, psSubspecies>, int}>
     */
    public function validNamesDoesntContainDuplicatesDataProvider(): array
    {
        return [[
            [
                'top1' => ['middle' => ['bottom' => []]],
                'top2' => ['bottom' => []],
            ],
            4,
        ], [
            [
                'top1' => ['middle' => ['top2' => []]],
                'top2' => ['bottom' => []],
            ],
            4,
        ]];
    }

    public function testDeepNestedDoesntCauseFatalError(): void
    {
        $species = ['a' => ['b' => ['c' => ['d' => ['e' => ['f' => ['g' => ['h' => ['i' => ['j' => ['k' => ['l' => [
            'm' => ['n' => []]]]]]]]], 'f2' => ['g2' => ['h2' => ['i2' => ['j2' => ['k2' => ['l2' => ['m2' => [
            'n2' => []]]]]]]]]]]]]]];

        $result = SpeciesBuilder::for($species);

        $descendants = $result->list->getByName('a')->getDescendants();

        self::assertCount(22, $descendants);
    }

    public function testProperlyBuilding(): void
    {
        $species = [
            'root1' => ['middle' => ['leaf1' => []]],
            'i_root2' => ['middle' => ['i_leaf2' => []]],
            'i_root3' => ['leaf3' => []],
        ];

        $result = SpeciesBuilder::for($species);

        self::assertEquals(['root1', 'middle', 'leaf1', 'root2', 'leaf2', 'root3', 'leaf3'], $result->list->getNames());
        self::assertEquals(['leaf1', 'leaf2', 'leaf3', 'middle', 'root1', 'root2', 'root3'], self::sortedNames($result->list));

        self::assertCount(3, $result->tree);

        $cRoot1 = $result->tree[0];
        self::assertEquals('root1', $cRoot1);
        self::assertEmpty($cRoot1->getParents());
        self::assertCount(1, $cRoot1->getChildren());

        $cRoot2 = $result->tree[1];
        self::assertEquals('root2', $cRoot2);
        self::assertEmpty($cRoot2->getParents());
        self::assertEquals($cRoot1->getChildren(), $cRoot2->getChildren());

        $cRoot3 = $result->tree[2];
        self::assertEquals('root3', $cRoot3);
        self::assertEmpty($cRoot3->getParents());
        self::assertCount(1, $cRoot3->getChildren());
        self::assertNotEquals($cRoot1->getChildren(), $cRoot3->getChildren());

        $cMiddle = $cRoot1->getChildren()[0];
        self::assertEquals('middle', $cMiddle);
        self::assertEquals([$cRoot1, $cRoot2], $cMiddle->getParents());
        self::assertCount(2, $cMiddle->getChildren());

        $cLeaf1 = $cMiddle->getChildren()[0];
        self::assertEquals('leaf1', $cLeaf1);
        self::assertEquals([$cMiddle], $cLeaf1->getParents());
        self::assertEmpty($cLeaf1->getChildren());

        $cLeaf2 = $cMiddle->getChildren()[1];
        self::assertEquals('leaf2', $cLeaf2);
        self::assertEquals([$cMiddle], $cLeaf2->getParents());
        self::assertEmpty($cLeaf2->getChildren());

        $cLeaf3 = $cRoot3->getChildren()[0];
        self::assertEquals('leaf3', $cLeaf3);
        self::assertEquals([$cRoot3], $cLeaf3->getParents());
        self::assertEmpty($cLeaf3->getChildren());
    }

    /**
     * @return list<string>
     */
    private function sortedNames(SpeciesList $species): array
    {
        return Vec\sort($species->getNames());
    }
}
