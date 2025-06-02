<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Parse;
use App\Utils\ParseException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider as TestDataProvider;

#[Small]
class ParseTest extends TestCase
{
    #[DataProvider('intDataProvider')]
    public function testInt(float|int|string|null $input, int|false $expectedInt): void
    {
        try {
            self::assertSame($expectedInt, Parse::int($input));
        } catch (ParseException) {
            self::assertFalse($expectedInt);
        }
    }

    public static function intDataProvider(): TestDataProvider
    {
        return TestDataProvider::tuples(
            [null,   false],
            [0,      0],
            [1,      1],
            [-1,     -1],
            [0.1,    false],
            [1.1,    false],
            [-1.1,   false],
            ['',     false],
            [' ',    false],
            ['0',    0],
            ['0 ',   false],
            ['1',    1],
            [' 1',   false],
            ['-1',   -1],
            ['-1 ',  false],
            ['1a',   false],
            ['1a ',  false],
            ['1.0',  false],
            [' 1.0', false],
        );
    }

    #[DataProvider('nBoolDataProvider')]
    public function testNBool(string $input, ?bool $expected): void
    {
        self::assertSame($expected, Parse::nBool($input));
    }

    public static function nBoolDataProvider(): TestDataProvider
    {
        return TestDataProvider::tuples(
            ['1',       true],
            ['true',    true],
            ['tRue',    true],
            ['TRUE',    true],
            ['on',      true],
            ['oN',      true],
            ['ON',      true],
            ['yes',     true],
            ['yEs',     true],
            ['YES',     true],
            ['0',       false],
            ['false',   false],
            ['fAlse',   false],
            ['FALSE',   false],
            ['off',     false],
            ['oFf',     false],
            ['OFF',     false],
            ['no',      false],
            ['nO',      false],
            ['NO',      false],
            ['',        null],
            ['null',    null],
            ['unknown', null],
            ['2',       null],
        );
    }
}
