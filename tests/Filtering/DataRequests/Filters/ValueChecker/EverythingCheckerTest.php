<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests\Filters\ValueChecker;

use App\Filtering\DataRequests\Filters\ValueChecker\EverythingChecker;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @small
 */
class EverythingCheckerTest extends TestCase
{
    /**
     * @dataProvider matchesDataProvider
     */
    public function testMatches(string $items, ?bool $matchedOther, bool $expected): void
    {
        $subject = new EverythingChecker(['A', 'B']);

        self::assertEquals($expected, $subject->matches($items, $matchedOther));
    }

    public function matchesDataProvider(): DataProvider
    {
        return DataProvider::tuples(
        // items, matchedOther, expected
            ['', true, false],
            ['', false, false],
            ['', null, false],

            ['A', true, false],
            ['A', false, false],
            ['A', null, false],

            ['B', true, false],
            ['B', false, false],
            ['B', null, false],

            ['AB', true, false],
            ['AB', false, false],
            ['AB', null, false],

            ["A\nB", true, true],
            ["A\nB", false, false],
            ["A\nB", null, true],
        );
    }
}
