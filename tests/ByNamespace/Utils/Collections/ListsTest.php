<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Utils\Collections;

use App\Utils\Collections\Lists;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class ListsTest extends TestCase
{
    public function testIntersect(): void
    {
        self::assertSame(['aaa', 'ccc'], Lists::intersect(
            ['aaa', 'bbb', 'ccc', 'ddd'],
            ['aaa', 'eee', 'ccc', 'fff'],
        ));
        self::assertSame(['aaa', 2 => 'ccc'], array_intersect(
            ['aaa', 'bbb', 'ccc', 'ddd'],
            ['aaa', 'eee', 'ccc', 'fff'],
        ));

        self::assertSame(['3'], Lists::intersect(
            ['3', '2', '1', ''],
            ['3', 2, true, false],
        ));
        self::assertSame(['3', '2', '1', ''], array_intersect(
            ['3', '2', '1', ''],
            ['3', 2, true, false],
        ));
    }
}
