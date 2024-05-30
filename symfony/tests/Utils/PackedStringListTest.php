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
}
