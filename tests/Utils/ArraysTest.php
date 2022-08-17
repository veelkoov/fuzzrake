<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Arrays;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ArraysTest extends TestCase
{
    public function testIntersect(): void
    {
        self::assertEqualsCanonicalizing(['aaa', 'ccc'], Arrays::intersect(
            ['aaa', 'bbb', 'ccc', 'ddd'],
            ['aaa', 'eee', 'ccc', 'fff'],
        ));
        self::assertEqualsCanonicalizing(['aaa', 'ccc'], array_intersect(
            ['aaa', 'bbb', 'ccc', 'ddd'],
            ['aaa', 'eee', 'ccc', 'fff'],
        ));

        self::assertEqualsCanonicalizing(['3'], Arrays::intersect(
            ['3', '2', '1', ''],
            ['3', 2, true, false],
        ));
        self::assertEqualsCanonicalizing(['3', '2', '1', ''], array_intersect(
            ['3', '2', '1', ''],
            ['3', 2, true, false],
        ));
    }

    public function testSingle(): void
    {
        self::assertEquals(10, Arrays::single([10]));

        try {
            Arrays::single([]);
            self::fail('Did not throw on empty array'); // @phpstan-ignore-line That's what's being tested here
        } catch (InvalidArgumentException) {
        }

        try {
            Arrays::single([10, 20]);
            self::fail('Did not throw on array with > 1 items');
        } catch (InvalidArgumentException) {
        }
    }
}
