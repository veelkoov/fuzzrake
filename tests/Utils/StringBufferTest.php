<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\StringBuffer;
use Composer\Pcre\PcreException;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Throwable;
use UnexpectedValueException;

#[Small]
class StringBufferTest extends TestCase
{
    public function testIsEmpty(): void
    {
        self::assertTrue(new StringBuffer('')->isEmpty());
        self::assertFalse(new StringBuffer(' ')->isEmpty());
    }

    public function testReadCharacter(): void
    {
        $buffer = new StringBuffer('ABC');
        self::assertSame('A', $buffer->readCharacter());
        self::assertSame('B', $buffer->readCharacter());
    }

    public function testReadUntilRegexpSpecialCases(): void
    {
        $subject = new StringBuffer('ABCDqwerZXCVqwerTYUIqwerFGHJ');

        self::assertSame('ABCD', $subject->readUntilRegexp('[qw]{1,}'));
        self::assertSame('erZXCVqwerTYUIqwerFGHJ', $subject->peekAll());

        try {
            $result = $subject->readUntilRegexp('[qw');
        } catch (Throwable $exception) {
            $result = $exception;
        }

        self::assertInstanceOf(PcreException::class, $result);

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

        self::assertSame('ABCD ', $subject->readUntilRegexp('[qwer]{1,}', true));
        self::assertSame('ZXCV qw TYUI', $subject->peekAll());

        self::assertSame('ZXCV ', $subject->readUntilRegexp('[qwer]{1,}', false));
        self::assertSame(' TYUI', $subject->peekAll());
    }
}
