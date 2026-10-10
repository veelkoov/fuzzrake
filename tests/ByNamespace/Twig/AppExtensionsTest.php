<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Twig;

use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\Item;
use App\Twig\AppExtensions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class AppExtensionsTest extends TestCase
{
    public function testFilterItemsMatchingFilter(): void
    {
        $subject = new AppExtensions();

        $input = new ItemList([
            // anyTHIng won't match
            new Item('anything1', 'something', 0, 0.0),
            new Item('anything2', 'will not match', 0, 0.0),
        ]);

        $result = $subject->filterItemsMatchingFilter($input, 'ThI');

        self::assertSame('anything1', $result->single()->value);
    }

    #[DataProvider('glueHtmlPrefixDataProvider')]
    public function testGlueHtmlPrefix(string $input, string $expected): void
    {
        $subject = new AppExtensions();

        self::assertSame($expected, $subject->glueHtmlPrefix($input, 'PREFIX'));
    }

    /**
     * @return list<array{string, string}>
     */
    public static function glueHtmlPrefixDataProvider(): array
    {
        return [
            ['A thing something something', '<span class="text-nowrap">PREFIX A thing</span> something something'],
            ['An other thing', '<span class="text-nowrap">PREFIX An other</span> thing'],
            ['The other thing', '<span class="text-nowrap">PREFIX The other</span> thing'],
            ['Some other thing', '<span class="text-nowrap">PREFIX Some</span> other thing'],

            ['', '<span class="text-nowrap">PREFIX </span>'],

            ['No', '<span class="text-nowrap">PREFIX No</span>'],
            ['Any', '<span class="text-nowrap">PREFIX Any</span>'],
            ['SPAM', '<span class="text-nowrap">PREFIX SPAM</span>'],
            ['Something', '<span class="text-nowrap">PREFIX Something</span>'],
        ];
    }
}
