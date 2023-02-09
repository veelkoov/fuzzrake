<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataProvider\Filters;

use App\Filtering\DataRequests\Filters\SpeciesFilter;
use App\Filtering\DataRequests\Filters\SpeciesSearchResolver;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class SpeciesFilterTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     *
     * @param list<string> $searched
     */
    public function testMatches(string $does, string $doesnt, array $searched, bool $matched): void
    {
        $resolver = $this->createMock(SpeciesSearchResolver::class);
        $resolver->method('resolveDoes')->willReturnCallback(
            fn (string $does, string $doesnt) => array_diff(StringList::unpack($does), StringList::unpack($doesnt))
        );

        $subject = new SpeciesFilter($searched, $resolver);
        $artisan = Artisan::new()->setSpeciesDoes($does)->setSpeciesDoesnt($doesnt);

        self::assertEquals($matched, $subject->matches($artisan));
    }

    /**
     * @return list<array{string, string, list<string>, bool}>
     */
    public function matchesProvider(): array
    {
        return [ // does, doesn't, searched, matched
            ['',        '',  [],    false],
            ['A',       '',  [],    false],
            ['A',       '',  ['A'], true],
            ["A\nB\nC", 'B', ['A'], true],
            ["A\nB\nC", 'B', ['C'], true],
            ["A\nB\nC", 'B', ['B'], false],
        ];
    }
}
