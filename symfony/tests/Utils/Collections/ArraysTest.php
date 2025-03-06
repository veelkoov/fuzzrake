<?php

declare(strict_types=1);

namespace App\Tests\Utils\Collections;

use App\Utils\Collections\Arrays;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class ArraysTest extends TestCase
{
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
