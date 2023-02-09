<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataProvider\Filters\ValueChecker;

use App\Filtering\DataRequests\Filters\ValueChecker\AnythingChecker;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class AnythingCheckerTest extends TestCase
{
    /**
     * @dataProvider matchesDataProvider
     */
    public function testMatches(string $items, ?bool $matchedOther, bool $expected): void
    {
        $subject = new AnythingChecker(['A', 'B']);

        self::assertEquals($expected, $subject->matches($items, $matchedOther));
    }

    /**
     * @return list<array{string, ?bool, bool}>
     */
    public function matchesDataProvider(): array
    {
        return [ // items, matchedOther, expected
            ['', true,  true],
            ['', false, false],
            ['', null,  false],

            ['A', true,  true],
            ['A', false, true],
            ['A', null,  true],

            ['B', true,  true],
            ['B', false, true],
            ['B', null,  true],

            ['AB', true,  true],
            ['AB', false, false],
            ['AB', null,  false],

            ["A\nB", true,  true],
            ["A\nB", false, true],
            ["A\nB", null,  true],
        ];
    }
}
