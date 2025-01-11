<?php

declare(strict_types=1);

namespace App\Tests\Utils\Arrays;

use App\Utils\Arrays\Lists;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class ListsTest extends TestCase
{
    public function testIntersect(): void
    {
        self::assertEqualsCanonicalizing(['aaa', 'ccc'], Lists::intersect(
            ['aaa', 'bbb', 'ccc', 'ddd'],
            ['aaa', 'eee', 'ccc', 'fff'],
        ));
        self::assertEqualsCanonicalizing(['aaa', 'ccc'], array_intersect(
            ['aaa', 'bbb', 'ccc', 'ddd'],
            ['aaa', 'eee', 'ccc', 'fff'],
        ));

        self::assertEqualsCanonicalizing(['3'], Lists::intersect(
            ['3', '2', '1', ''],
            ['3', 2, true, false],
        ));
        self::assertEqualsCanonicalizing(['3', '2', '1', ''], array_intersect(
            ['3', '2', '1', ''],
            ['3', 2, true, false],
        ));
    }
}
