<?php

declare(strict_types=1);

namespace App\Tests\Utils\Species;

use App\Utils\Species\HierarchyAwareBuilder;
use App\Utils\Species\Specie;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

/**
 * @small
 */
class HierarchyAwareBuilderTest extends TestCase
{
    /**
     * @param array<string, psSubspecies> $species
     *
     * @dataProvider validNamesDoesntContainDuplicatesDataProvider
     */
    public function testValidNamesDoesntContainDuplicates(array $species, int $expectedCount): void
    {
        $subject = new HierarchyAwareBuilder($species);

        self::assertCount($expectedCount, $subject->getValidNames());
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
        self::expectNotToPerformAssertions();

        $subject = new HierarchyAwareBuilder(['a' => ['b' => ['c' => ['d' => ['e' => [
            'f'  => ['g' => ['h' => ['i' => ['j' => ['k' => ['l' => ['m' => ['n' => []]]]]]]]],
            'f2' => ['g2' => ['h2' => ['i2' => ['j2' => ['k2' => ['l2' => ['m2' => ['n2' => []]]]]]]]],
        ]]]]]]);

        $species = $subject->getVisibleList();
        $species['a']->getDescendants();
        $species['n2']->isDescendantOf($species['a']);
    }

    public function testProperlyBuilding(): void
    {
        $subject = new HierarchyAwareBuilder([
            'root1'   => ['middle' => ['leaf1' => []]],
            'i_root2' => ['middle' => ['i_leaf2' => []]],
            'i_root3' => ['leaf3'  => []],
        ]);

        self::assertEquals(['root1', 'middle', 'leaf1', 'root2', 'leaf2', 'root3', 'leaf3'], $subject->getValidNames());
        self::assertEquals(['leaf1', 'leaf2', 'leaf3', 'middle', 'root1', 'root2', 'root3'], self::sortedNames($subject->getCompleteList()));
        self::assertEquals(['leaf1', 'leaf3', 'middle', 'root1'], self::sortedNames($subject->getVisibleList()));

        self::assertCount(3, $subject->getCompleteTree());

        $cRoot1 = $subject->getCompleteTree()[0];
        self::assertEquals('root1', $cRoot1);
        self::assertEmpty($cRoot1->getParents());
        self::assertCount(1, $cRoot1->getChildren());

        $cRoot2 = $subject->getCompleteTree()[1];
        self::assertEquals('root2', $cRoot2);
        self::assertEmpty($cRoot2->getParents());
        self::assertEquals($cRoot1->getChildren(), $cRoot2->getChildren());

        $cRoot3 = $subject->getCompleteTree()[2];
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

        self::assertCount(1, $subject->getVisibleTree());

        $vRoot1 = $subject->getVisibleTree()[0];
        self::assertNotEquals($vRoot1, $cRoot1);
        self::assertEquals('root1', $vRoot1);
        self::assertEmpty($vRoot1->getParents());
        self::assertCount(1, $vRoot1->getChildren());

        $vMiddle = $vRoot1->getChildren()[0];
        self::assertNotEquals($vMiddle, $cMiddle);
        self::assertEquals('middle', $vMiddle);
        self::assertEquals([$vRoot1], $vMiddle->getParents());
        self::assertCount(1, $vMiddle->getChildren());

        $vLeaf1 = $vMiddle->getChildren()[0];
        self::assertNotEquals($vLeaf1, $cLeaf1);
        self::assertEquals('leaf1', $vLeaf1);
        self::assertEquals([$vMiddle], $vLeaf1->getParents());
        self::assertEmpty($vLeaf1->getChildren());
    }

    /**
     * @param Specie[] $species
     *
     * @return list<string>
     */
    private function sortedNames(array $species): array
    {
        return Vec\sort(Vec\map($species, fn (Specie $specie) => $specie->getName()));
    }
}
