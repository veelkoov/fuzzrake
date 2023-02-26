<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataRequests\Filters;

use App\Filtering\DataRequests\Filters\ProductionModelsFilter;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class ProductionModelsFilterTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     *
     * @param string[] $searched
     */
    public function testMatches(string $productionModels, array $searched, bool $matched): void
    {
        $subject = new ProductionModelsFilter($searched);
        $artisan = Artisan::new()->setProductionModels($productionModels);

        self::assertEquals($matched, $subject->matches($artisan));
    }

    /**
     * @return list<array{string, string[], bool}>
     */
    public function matchesProvider(): array
    {
        return [
            ["Item1\nItem2", [], false],
            ["Item1\nItem2", ['Item1'], true],
            ["Item1\nItem2", ['Item1', 'Item2'], true],
            ['Item1', ['Item1', 'Item2'], true],

            ['', ['?'], true],
            ['', ['?', 'Item1'], true],
            ['Item1', ['?'], false],

            ['', ['*'], false],
            ['Item1', ['*'], false],
            ['Item1', ['Item1', '*'], true],
            ['', ['Item1', '*'], false],

            ['', ['?', '*'], true],
            ['Item1', ['?', '*'], false],
            ['Item1', ['?', '*', 'Item1'], true],
        ];
    }
}
