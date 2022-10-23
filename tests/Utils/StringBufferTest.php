<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\StringBuffer;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

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

    public function testReadUntilRegexpSpecialCases(): void
    {
        $subject = new StringBuffer('ABCDqwerZXCVqwerTYUIqwerFGHJ');

        self::assertEquals('ABCD', $subject->readUntilRegexp('[qw]{1,}'));
        self::assertEquals('erZXCVqwerTYUIqwerFGHJ', $subject->peekAll());

        try {
            $result = $subject->readUntilRegexp('[qw');
        } catch (Throwable $exception) {
            $result = $exception;
        }

        self::assertInstanceOf(RuntimeException::class, $result);

        try {
            $result = $subject->readUntilRegexp('[12]{1,}');
        } catch (Throwable $exception) {
            $result = $exception;
        }

        self::assertInstanceOf(UnexpectedValueException::class, $result);
    }

    public function testReadUntilRegexpWhitespace(): void
    {
        $subject = new StringBuffer('ABCD qw ZXCV qw TYUI');

        self::assertEquals('ABCD ', $subject->readUntilRegexp('[qwer]{1,}', true));
        self::assertEquals('ZXCV qw TYUI', $subject->peekAll());

        self::assertEquals('ZXCV ', $subject->readUntilRegexp('[qwer]{1,}', false));
        self::assertEquals(' TYUI', $subject->peekAll());
    }
}
