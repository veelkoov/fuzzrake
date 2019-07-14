<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\StrContextUtils;
use PHPUnit\Framework\TestCase;

class StrContextUtilsTest extends TestCase
{
    /**
     * @dataProvider createFromDataProvider
     *
     * @param string $input
     * @param string $match
     * @param int    $contextLength
     * @param string $before
     * @param string $subject
     * @param string $after
     */
    public function testExtractFrom(string $input, string $match, int $contextLength, string $before, string $subject, string $after): void
    {
        $strContext = StrContextUtils::extractFrom($input, $match, $contextLength);

        $this->assertEquals($before, $strContext->getBefore());
        $this->assertEquals($subject, $strContext->getSubject());
        $this->assertEquals($after, $strContext->getAfter());
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
