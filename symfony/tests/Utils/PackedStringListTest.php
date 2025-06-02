<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\PackedStringList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use TRegx\PhpUnit\DataProviders\DataProvider as TestDataProvider;

#[Small]
class PackedStringListTest extends TestCase
{
    /**
     * @param string[] $nonsplittables
     * @param string[] $expectedResult
     */
    #[DataProvider('splitDataProvider')]
    public function testSplit(string $input, string $separatorRegexp, array $nonsplittables, array $expectedResult): void
    {
        self::assertEquals($expectedResult, PackedStringList::split($input, $separatorRegexp, $nonsplittables));
    }

    public static function splitDataProvider(): TestDataProvider
    {
        return TestDataProvider::tuples(
            [
                'dog, wolf, fox, big and small cats, birds, bats, dragons, skullsuits...',
                "[\n,.]",
                ['skullsuits...'],
                ['dog', ' wolf', ' fox', ' big and small cats', ' birds', ' bats', ' dragons', ' skullsuits...'],
            ]
        );
    }
}
