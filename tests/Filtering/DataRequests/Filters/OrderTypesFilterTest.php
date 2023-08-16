<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests\Filters;

use App\Filtering\DataRequests\Filters\OrderTypesFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class OrderTypesFilterTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     *
     * @param string[] $searched
     */
    public function testMatches(string $orderTypes, string $otherOrderTypes, array $searched, bool $matched): void
    {
        $subject = new OrderTypesFilter($searched);
        $artisan = Artisan::new()->setOrderTypes($orderTypes)->setOtherOrderTypes($otherOrderTypes);

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
