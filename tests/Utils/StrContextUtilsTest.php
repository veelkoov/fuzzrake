<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\StrContextUtils;
use PHPUnit\Framework\TestCase;

class StrContextUtilsTest extends TestCase
{
    /**
     * @noinspection PhpTooManyParametersInspection
     * @dataProvider createFromDataProvider
     */
    public function testExtractFrom(string $input, string $match, int $contextLength, string $before, string $subject, string $after): void
    {
        $strContext = StrContextUtils::extractFrom($input, $match, $contextLength);

        static::assertEquals($before, $strContext->getBefore());
        static::assertEquals($subject, $strContext->getSubject());
        static::assertEquals($after, $strContext->getAfter());
    }

    public function createFromDataProvider(): array
    {
        return [
            ['abcdefghijk', 'efg', 3, 'bcd', 'efg', 'hij'],
            ['abcdefghijk', 'def', 3, 'abc', 'def', 'ghi'],
            ['abcdefghijk', 'def', 4, 'abc', 'def', 'ghij'],
            ['abcdefghijk', 'fgh', 4, 'bcde', 'fgh', 'ijk'],
            ['abcdefghijk', 'ijk', 8, 'abcdefgh', 'ijk', ''],
            ['abcdefghijk', 'hij', 8, 'abcdefg', 'hij', 'k'],
            ['abcdefghijk', 'abc', 8, '', 'abc', 'defghijk'],
            ['abcdefghijk', 'bcd', 8, 'a', 'bcd', 'efghijk'],
            ['abcdefghijk', 'abcdefghijk', 2, '', 'abcdefghijk', ''],
            ['abcdefghijk', 'def', 0, '', 'def', ''],
        ];
    }
}
