<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataProvider\Filters;

use App\Filtering\DataProvider\Filters\OrderTypesFilter;
use App\Filtering\DataProvider\Filters\StylesFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class StylesFilterTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     *
     * @param string[] $searched
     */
    public function testMatches($styles, $otherStyles, array $searched, bool $matched): void
    {
        $subject = new StylesFilter($searched);
        $artisan = Artisan::new()->setStyles($styles)->setOtherStyles($otherStyles);

        self::assertEquals($matched, $subject->matches($artisan));
    }

    /**
     * @return list<array{string, string, string[], bool}>
     */
    public function matchesProvider(): array
    {
        return [
            ["Item1\nItem2", '', [], false],
            ["Item1\nItem2", '', ['Item1'], true],
            ["Item1\nItem2", '', ['Item1', 'Item2'], true],
            ['Item1', '', ['Item1', 'Item2'], true],

            ['', '', ['?'], true],
            ['', '', ['?', 'Item1'], true],
            ['Item1', '', ['?'], false],
            ['', 'OtherItem1', ['?'], false],

            ['', 'OtherItem1', ['*'], true],
            ['Item1', '', ['*'], false],
            ['Item1', 'OtherItem1', ['Item1', '*'], true],
            ['Item1', '', ['Item1', '*'], true],
            ['', 'OtherItem1', ['Item1', '*'], true],

            ['', '', ['?', '*'], true],
            ['', 'OtherItem1', ['?', '*'], true],
            ['Item1', 'OtherItem1', ['?', '*'], true],
            ['Item1', 'OtherItem1', ['?', '*', 'Item1'], true],
            ['Item1', '', ['?', '*', 'Item1'], true],
        ];
    }
}
