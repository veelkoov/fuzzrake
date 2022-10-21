<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\StringList;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class StringListTest extends TestCase
{
    /**
     * @dataProvider splitDataProvider
     *
     * @param string[] $nonsplittables
     * @param string[] $expectedResult
     */
    public function testSplit(string $input, string $separatorRegexp, array $nonsplittables, array $expectedResult): void
    {
        self::assertEquals($expectedResult, StringList::split($input, $separatorRegexp, $nonsplittables));
    }

    public function splitDataProvider(): array // @phpstan-ignore-line
    {
        return [
            [
                'dog, wolf, fox, big and small cats, birds, bats, dragons, skullsuits...', "[\n,.]", ['skullsuits...'],
                ['dog', ' wolf', ' fox', ' big and small cats', ' birds', ' bats', ' dragons', ' skullsuits...'],
            ],
        ];
    }

    /**
     * @dataProvider sameElementsDataProvider
     */
    public function testSameElements(bool $expected, string $input1, string $input2): void
    {
        self::assertEquals($expected, StringList::sameElements($input1, $input2));
    }

    public function sameElementsDataProvider(): array // @phpstan-ignore-line
    {
        return [
            [true, 'abc', 'abc'],
            [true, "abc\ndef", "abc\ndef"],
            [true, "abc\ndef\nghi", "def\nghi\nabc"],
            [false, "abc\ndef", "abc\ndef\nghi"],
            [false, "abc\ndef\nghi", "def\nghi"],
            [false, "abc\ndef", "abC\ndef"],
        ];
    }
}
