<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\PackedStringList;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider;

/**
 * @small
 */
class PackedStringListTest extends TestCase
{
    /**
     * @dataProvider splitDataProvider
     *
     * @param string[] $nonsplittables
     * @param string[] $expectedResult
     */
    public function testSplit(string $input, string $separatorRegexp, array $nonsplittables, array $expectedResult): void
    {
        self::assertEquals($expectedResult, PackedStringList::split($input, $separatorRegexp, $nonsplittables));
    }

    public function splitDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            [
                'dog, wolf, fox, big and small cats, birds, bats, dragons, skullsuits...',
                "[\n,.]",
                ['skullsuits...'],
                ['dog', ' wolf', ' fox', ' big and small cats', ' birds', ' bats', ' dragons', ' skullsuits...'],
            ]
        );
    }

    /**
     * @dataProvider sameElementsDataProvider
     */
    public function testSameElements(bool $expected, string $input1, string $input2): void
    {
        self::assertEquals($expected, PackedStringList::sameElements($input1, $input2));
    }

    public function sameElementsDataProvider(): DataProvider
    {
        return DataProvider::tuples(
            [true, 'abc', 'abc'],
            [true, "abc\ndef", "abc\ndef"],
            [true, "abc\ndef\nghi", "def\nghi\nabc"],
            [false, "abc\ndef", "abc\ndef\nghi"],
            [false, "abc\ndef\nghi", "def\nghi"],
            [false, "abc\ndef", "abC\ndef"],
        );
    }
}
