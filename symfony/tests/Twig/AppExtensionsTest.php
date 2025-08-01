<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Filtering\FiltersData\Data\ItemList;
use App\Filtering\FiltersData\Item;
use App\Twig\AppExtensions;
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
            new Item('anything1', 'something', 0),
            new Item('anything2', 'will not match', 0),
        ]);

        $result = $subject->filterItemsMatchingFilter($input, 'ThI');

        self::assertSame('anything1', $result->single()->value);
    }
}
