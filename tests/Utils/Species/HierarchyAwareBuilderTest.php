<?php

declare(strict_types=1);

namespace App\Tests\Utils\Species;

use App\Utils\Species\HierarchyAwareBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class HierarchyAwareBuilderTest extends TestCase
{
    /**
     * @param array<string, psSpecie> $species
     *
     * @dataProvider validNamesDoesntContainDuplicatesDataProvider
     */
    public function testValidNamesDoesntContainDuplicates(array $species, int $expectedCount): void
    {
        $subject = new HierarchyAwareBuilder($species);

        self::assertCount($expectedCount, $subject->getValidNames());
    }

    /**
     * @return array<int, array{0: array<string, psSpecie>, 1: int}>
     */
    public function validNamesDoesntContainDuplicatesDataProvider(): array
    {
        return [
            [
                [
                    'top1' => [
                        'middle' => [
                            'bottom' => [], ], ],
                    'top2' => [
                        'bottom' => [], ],
                ],
                4,
            ],
            [
                [
                    'top1' => [
                        'middle' => [
                            'top2' => [], ], ],
                    'top2' => [
                        'bottom' => [], ],
                ],
                4,
            ],
        ];
    }

    public function testDeepNestedDoesntCauseFatalError(): void
    {
        self::expectNotToPerformAssertions();

        $subject = new HierarchyAwareBuilder(['a' => ['b' => ['c' => ['d' => ['e' => [
            'f'  => ['g' => ['h' => ['i' => ['j' => ['k' => ['l' => ['m' => ['n' => []]]]]]]]],
            'f2' => ['g2' => ['h2' => ['i2' => ['j2' => ['k2' => ['l2' => ['m2' => ['n2' => []]]]]]]]],
        ]]]]]]);

        $species = $subject->getFlat();
        $species['a']->getDescendants();
        $species['n2']->isDescendantOf($species['a']);
    }
}
