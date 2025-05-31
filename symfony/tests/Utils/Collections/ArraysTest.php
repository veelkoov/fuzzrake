<?php

declare(strict_types=1);

namespace App\Tests\Utils\Collections;

use App\Utils\Collections\Arrays;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class ArraysTest extends TestCase
{
    public function testSingle(): void
    {
        self::assertSame(10, Arrays::single([10])); // @phpstan-ignore staticMethod.alreadyNarrowedType (Being tested here)

        try {
            Arrays::single([]);
            self::fail('Did not throw on empty array'); // @phpstan-ignore deadCode.unreachable (Being tested here)
        } catch (InvalidArgumentException) {
        }

        try {
            Arrays::single([10, 20]);
            self::fail('Did not throw on array with > 1 items');
        } catch (InvalidArgumentException) {
        }
    }
}
