<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests\Filters;

use App\Filtering\DataRequests\Filters\StylesFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

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
    public function testMatches(string $styles, string $otherStyles, array $searched, bool $matched): void
    {
        $subject = new StylesFilter($searched);
        $artisan = Artisan::new()->setStyles($styles)->setOtherStyles($otherStyles);

        self::assertEquals($matched, $subject->matches($artisan));
    }

    public function matchesProvider(): DataProvider
    {
        return DataProvider::tuples(
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
        );
    }
}
