<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Parse;
use App\Utils\ParseException;
use PHPUnit\Framework\TestCase;

class ParseTest extends TestCase
{
    /**
     * @dataProvider intDataProvider
     *
     * @param $input
     * @param $expectedInt
     * @param $expectedTInt
     */
    public function testIntAndTInt($input, $expectedInt, $expectedTInt): void
    {
        try {
            self::assertSame($expectedInt, Parse::int($input));
        } catch (ParseException $e) {
            self::assertFalse($expectedInt);
        }

        try {
            self::assertSame($expectedTInt, Parse::tInt($input));
        } catch (ParseException $e) {
            self::assertFalse($expectedTInt);
        }
    }

    /**
     * @dataProvider floatDataProvider
     *
     * @param $input
     * @param $expectedFloat
     * @param $expectedTFloat
     */
    public function testFloatAndTFloat($input, $expectedFloat, $expectedTFloat): void
    {
        try {
            self::assertSame($expectedFloat, Parse::float($input));
        } catch (ParseException $e) {
            self::assertFalse($expectedFloat);
        }

        try {
            self::assertSame($expectedTFloat, Parse::tFloat($input));
        } catch (ParseException $e) {
            self::assertFalse($expectedTFloat);
        }
    }

    /**
     * @dataProvider percentAsIntDataProvider
     *
     * @param $input
     * @param $expectedPercentAsInt
     * @param $expectedTPercentAsInt
     */
    public function testPercentAsIntAndTPercentAsInt($input, $expectedPercentAsInt, $expectedTPercentAsInt): void
    {
        try {
            self::assertSame($expectedPercentAsInt, Parse::percentAsInt($input));
        } catch (ParseException $e) {
            self::assertFalse($expectedPercentAsInt);
        }

        try {
            self::assertSame($expectedTPercentAsInt, Parse::tPercentAsInt($input));
        } catch (ParseException $e) {
            self::assertFalse($expectedTPercentAsInt);
        }
    }

    public function intDataProvider(): array
    {
        return [
            [null, false, false],
            [0, 0, 0],
            [1, 1, 1],
            [-1, -1, -1],
            [0.1, false, false],
            [1.1, false, false],
            [-1.1, false, false],
            ['', false, false],
            [' ', false, false],
            ['0', 0, 0],
            ['0 ', false, 0],
            ['1', 1, 1],
            [' 1', false, 1],
            ['-1', -1, -1],
            ['-1 ', false, -1],
            ['1a', false, false],
            ['1a ', false, false],
            ['1.0', false, false],
            [' 1.0', false, false],
        ];
    }

    public function percentAsIntDataProvider(): array
    {
        return [
            [null, false, false],
            ['', false, false],
            [' ', false, false],
            ['0%', 0, 0],
            ['0% ', false, 0],
            ['1%', 1, 1],
            [' 1%', false, 1],
            ['-1%', -1, -1],
            ['-1% ', false, -1],
            ['1%a', false, false],
            ['1%a ', false, false],
            ['1.0%', false, false],
            [' 1.0%', false, false],
        ];
    }

    public function floatDataProvider(): array
    {
        return [
            [null, false, false],
            ['', false, false],
            [' ', false, false],
            ['0', 0.0, 0.0],
            ['0 ', false, 0.0],
            ['1.0', 1.0, 1.0],
            ['1', 1.0, 1.0],
            ['1.0 ', false, 1.0],
            ['-1.0', -1.0, -1.0],
            ['-1.0 ', false, -1.0],
            ['1.', false, false],
            ['.1', false, false],
            ['e', false, false],
            ['-', false, false],
            ['-e', false, false],
            ['-.', false, false],
            ['.-', false, false],
            ['0.-', false, false],
            ['.0-', false, false],
            ['.-0', false, false],
            ['.0-0', false, false],
            ['0.0-', false, false],
            ['0.0-0', false, false],
        ];
    }
}
