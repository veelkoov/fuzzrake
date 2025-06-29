<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Utils\StrUtils;
use DateTimeImmutable;

trait AssertsTrait
{
    protected static function assertEqualsIgnoringWhitespace(string $expectedHtml, string $actualHtml): void
    {
        $pattern = pattern('\s+');

        $expectedHtml = trim($pattern->replace($expectedHtml)->with(' '));
        $actualHtml = trim($pattern->replace($actualHtml)->with(' '));

        self::assertSame($expectedHtml, $actualHtml);
    }

    /**
     * @param mixed[] $expected
     * @param mixed[] $actual
     */
    protected function assertArrayItemsSameOrderIgnored(array $expected, array $actual, string $message = ''): void
    {
        sort($expected);
        sort($actual);

        self::assertEquals($expected, $actual, $message);
    }

    protected static function assertDateTimeSameIgnoreSubSeconds(DateTimeImmutable $expected, ?DateTimeImmutable $actual): void
    {
        self::assertSame($expected->getTimezone()->getName(), $actual?->getTimezone()?->getName());
        self::assertSame(StrUtils::asStr($expected), StrUtils::asStr($actual));
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
}
