<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests\Filters\ValueChecker;

use App\Filtering\DataRequests\Filters\ValueChecker\AnythingChecker;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @small
 */
class AnythingCheckerTest extends TestCase
{
    /**
     * @param list<string> $items
     *
     * @dataProvider matchesDataProvider
     */
    public function testMatches(array $items, ?bool $matchedOther, bool $expected): void
    {
        $subject = new AnythingChecker(['A', 'B']);

        self::assertEquals($expected, $subject->matches($items, $matchedOther));
    }

    public function matchesDataProvider(): DataProvider
    {
        return DataProvider::tuples( // items, matchedOther, expected
            [[], true,  true],
            [[], false, false],
            [[], null,  false],

            [['A'], true,  true],
            [['A'], false, true],
            [['A'], null,  true],

            [['B'], true,  true],
            [['B'], false, true],
            [['B'], null,  true],

            [['AB'], true,  true],
            [['AB'], false, false],
            [['AB'], null,  false],

            [['A', 'B'], true,  true],
            [['A', 'B'], false, true],
            [['A', 'B'], null,  true],
        );
    }
}
