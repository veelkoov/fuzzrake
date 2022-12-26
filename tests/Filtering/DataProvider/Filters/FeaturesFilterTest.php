<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataProvider\Filters;

use App\Filtering\DataProvider\Filters\FeaturesFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class FeaturesFilterTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     *
     * @param string[] $searched
     */
    public function testMatches(string $features, string $otherFeatures, array $searched, bool $matched): void
    {
        $subject = new FeaturesFilter($searched);
        $artisan = Artisan::new()->setFeatures($features)->setOtherFeatures($otherFeatures);

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
            ['Item1', '', ['Item1', 'Item2'], false],

            ['', '', ['?'], true],
            ['', '', ['?', 'Item1'], true],
            ['Item1', '', ['?'], false],
            ['', 'OtherItem1', ['?'], false],

            ['', 'OtherItem1', ['*'], true],
            ['Item1', '', ['*'], false],
            ['Item1', 'OtherItem1', ['Item1', '*'], true],
            ['Item1', '', ['Item1', '*'], false],
            ['', 'OtherItem1', ['Item1', '*'], false],

            ['', '', ['?', '*'], true],
            ['', 'OtherItem1', ['?', '*'], true],
            ['Item1', 'OtherItem1', ['?', '*'], true],
            ['Item1', 'OtherItem1', ['?', '*', 'Item1'], true],
            ['Item1', '', ['?', '*', 'Item1'], false],
        ];
    }
}
