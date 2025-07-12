<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Utils\StrUtils;
use Composer\Pcre\Preg;
use DateTimeImmutable;

trait AssertsTrait
{
    protected static function assertEqualsIgnoringWhitespace(string $expectedHtml, string $actualHtml): void
    {
        $expectedHtml = trim(Preg::replace('~\s+~', ' ', $expectedHtml));
        $actualHtml = trim(Preg::replace('~\s+~', ' ', $actualHtml));

        self::assertSame($expectedHtml, $actualHtml);
    }

    /**
     * @param iterable<mixed> $expected
     * @param iterable<mixed> $actual
     */
    protected static function assertSameItems(iterable $expected, iterable $actual, string $message = ''): void
    {
        $expected = [...$expected];
        $actual = [...$actual];

        sort($expected);
        sort($actual);

        self::assertSame($expected, $actual, $message);
    }

    protected static function assertDateTimeSameIgnoreSubSeconds(DateTimeImmutable $expected, ?DateTimeImmutable $actual): void
    {
        self::assertSame($expected->getTimezone()->getName(), $actual?->getTimezone()?->getName());
        self::assertSame(StrUtils::asStr($expected), StrUtils::asStr($actual));
    }
}
