<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Parse;
use App\Utils\ParseException;
use PHPUnit\Framework\TestCase;

class ParseTest extends TestCase
{
    /**
     * @dataProvider intAndTIntDataProvider
     */
    public function testIntAndTInt($input, $expectedInt, $expectedTInt): void
    {
        try {
            self::assertSame($expectedInt, Parse::int($input));
        } catch (ParseException) {
            self::assertFalse($expectedInt);
        }

        try {
            self::assertSame($expectedTInt, Parse::tInt($input));
        } catch (ParseException) {
            self::assertFalse($expectedTInt);
        }
    }

    public function intAndTIntDataProvider(): array
    {
        return [
            [null,   false, false],
            [0,      0,     0],
            [1,      1,     1],
            [-1,     -1,    -1],
            [0.1,    false, false],
            [1.1,    false, false],
            [-1.1,   false, false],
            ['',     false, false],
            [' ',    false, false],
            ['0',    0,     0],
            ['0 ',   false, 0],
            ['1',    1,     1],
            [' 1',   false, 1],
            ['-1',   -1,    -1],
            ['-1 ',  false, -1],
            ['1a',   false, false],
            ['1a ',  false, false],
            ['1.0',  false, false],
            [' 1.0', false, false],
        ];
    }

    /**
     * @dataProvider floatAndTFloatDataProvider
     */
    public function testFloatAndTFloat($input, $expectedFloat, $expectedTFloat): void
    {
        try {
            self::assertSame($expectedFloat, Parse::float($input));
        } catch (ParseException) {
            self::assertFalse($expectedFloat);
        }

        try {
            self::assertSame($expectedTFloat, Parse::tFloat($input));
        } catch (ParseException) {
            self::assertFalse($expectedTFloat);
        }
    }

    public function floatAndTFloatDataProvider(): array
    {
        return [
            [null,    false, false],
            ['',      false, false],
            [' ',     false, false],
            ['0',     0.0,   0.0],
            ['0 ',    false, 0.0],
            ['1.0',   1.0,   1.0],
            ['1',     1.0,   1.0],
            ['1.0 ',  false, 1.0],
            ['-1.0',  -1.0,  -1.0],
            ['-1.0 ', false, -1.0],
            ['1.',    false, false],
            ['.1',    false, false],
            ['e',     false, false],
            ['-',     false, false],
            ['-e',    false, false],
            ['-.',    false, false],
            ['.-',    false, false],
            ['0.-',   false, false],
            ['.0-',   false, false],
            ['.-0',   false, false],
            ['.0-0',  false, false],
            ['0.0-',  false, false],
            ['0.0-0', false, false],
        ];
    }

    /**
     * @dataProvider percentAsIntAndTPercentAsIntDataProvider
     */
    public function testPercentAsIntAndTPercentAsInt($input, $expectedPercentAsInt, $expectedTPercentAsInt): void
    {
        try {
            self::assertSame($expectedPercentAsInt, Parse::percentAsInt($input));
        } catch (ParseException) {
            self::assertFalse($expectedPercentAsInt);
        }

        try {
            self::assertSame($expectedTPercentAsInt, Parse::tPercentAsInt($input));
        } catch (ParseException) {
            self::assertFalse($expectedTPercentAsInt);
        }
    }

    public function percentAsIntAndTPercentAsIntDataProvider(): array
    {
        return [
            [null,    false, false],
            ['',      false, false],
            [' ',     false, false],
            ['0%',    0,     0],
            ['0% ',   false, 0],
            ['1%',    1,     1],
            [' 1%',   false, 1],
            ['-1%',   -1,    -1],
            ['-1% ',  false, -1],
            ['1%a',   false, false],
            ['1%a ',  false, false],
            ['1.0%',  false, false],
            [' 1.0%', false, false],
        ];
    }

    /**
     * @dataProvider nBoolDataProvider
     */
    public function testNBool(string $input, ?bool $expected): void
    {
        self::assertEquals($expected, Parse::nBool($input));
    }

    public function nBoolDataProvider(): array
    {
        return [
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
        ];
    }
}
