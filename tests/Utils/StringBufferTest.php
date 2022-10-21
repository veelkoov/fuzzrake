<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\StringBuffer;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class StringBufferTest extends TestCase
{
    public function testIsEmpty(): void
    {
        self::assertTrue((new StringBuffer(''))->isEmpty());
        self::assertFalse((new StringBuffer(' '))->isEmpty());
    }

    public function testReadCharacter(): void
    {
        $buffer = new StringBuffer('ABC');
        self::assertEquals('A', $buffer->readCharacter());
        self::assertEquals('B', $buffer->readCharacter());
    }
}
