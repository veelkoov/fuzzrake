<?php

declare(strict_types=1);

namespace App\Tests\Utils\Species;

use App\Utils\Species\HierarchyAwareBuilder;
use PHPUnit\Framework\TestCase;

class HierarchyAwareBuilderTest extends TestCase
{
    /**
     * @dataProvider validNamesDoesntContainDuplicatesDataProvider
     */
    public function testValidNamesDoesntContainDuplicates(array $species, int $expectedCount): void
    {
        $subject = new HierarchyAwareBuilder($species);

        self::assertCount($expectedCount, $subject->getValidNames());
    }

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
}
