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
        $expectedHtml = trim(Preg::replace('#\s+#', ' ', $expectedHtml));
        $actualHtml = trim(Preg::replace('#\s+#', ' ', $actualHtml));

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
}
