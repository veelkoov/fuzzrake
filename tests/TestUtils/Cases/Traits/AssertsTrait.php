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
        self::assertSame(
            iter_sortl($expected),
            iter_sortl($actual),
            $message,
        );
    }

    protected static function assertDateTimeSameIgnoreSubSeconds(DateTimeImmutable $expected, ?DateTimeImmutable $actual): void
    {
        self::assertSame($expected->getTimezone()->getName(), $actual?->getTimezone()?->getName());
        self::assertSame(StrUtils::asStr($expected), StrUtils::asStr($actual));
    }

    protected static function assertSameEntity(?object $first, ?object $second): void
    {
        // This most probably means that the entity manager got cleared?
        // Or does it happen when one object gets persisted and second gets retrieved by findAll?

        self::assertNotNull($first);
        self::assertNotNull($second);

        if (!method_exists($first, 'getId') || !method_exists($second, 'getId')) {
            self::fail('Unable to compare object unless both implement getId().');
        }

        self::assertSame([$first->getId(), $first::class], [$second->getId(), $second::class]);
    }
}
